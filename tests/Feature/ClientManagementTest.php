<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Vite::partialMock()->shouldReceive('fonts')->andReturn(new HtmlString(''));
        Storage::fake('local');
    }

    public function test_authentication_is_required_for_clients_and_contacts(): void
    {
        $client = Client::factory()->create();
        $this->get(route('clients.index'))->assertRedirect(route('login'));
        $this->post(route('clients.store'), [])->assertRedirect(route('login'));
        $this->post(route('contacts.store', $client), [])->assertRedirect(route('login'));
    }

    public function test_clients_and_their_logos_persist_and_can_be_updated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('clients.store'), [
            'name' => 'Bosque compartido',
            'industry' => 'Diseño',
            'website' => 'https://example.com',
            'notes' => 'Queremos crecer juntos.',
            'status' => 'active',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'user_id' => 999,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $client = Client::firstOrFail();
        $this->assertSame($user->id, $client->user_id);
        Storage::disk('local')->assertExists($client->logo_path);
        $this->get(route('clients.logo', $client))->assertOk();
        $originalLogo = $client->logo_path;
        $this->put(route('clients.update', $client), [
            'name' => 'Bosque compartido', 'status' => 'paused',
            'logo' => UploadedFile::fake()->image('nuevo.png'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('clients.show', $client));
        $client->refresh();
        $this->assertSame('paused', $client->status);
        Storage::disk('local')->assertMissing($originalLogo);
        Storage::disk('local')->assertExists($client->logo_path);
        $this->get(route('clients.edit', $client))->assertOk()->assertSee('Bosque compartido');
    }

    public function test_multiple_contacts_can_be_saved_and_missing_photos_show_the_human_reminder(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($client->user);
        foreach (['María Luna', 'Juan Robles'] as $name) {
            $this->post(route('contacts.store', $client), ['name' => $name])
                ->assertSessionHasNoErrors()->assertRedirect(route('clients.show', $client));
        }
        $this->assertCount(2, $client->contacts);
        $this->get(route('clients.show', $client))->assertOk()
            ->assertSee('María Luna')->assertSee('Juan Robles')
            ->assertSee('Ponerle nombre a los rostros nos acerca.')
            ->assertSee('pídele autorización antes de tomar una captura de pantalla')
            ->assertSee('sitio web o de su perfil');
        $this->get(route('clients.index'))->assertOk()->assertSee('Ponerle nombre a los rostros nos acerca.');
    }

    public function test_contact_photos_require_authorization_and_survive_edits_without_a_new_photo(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($client->user);
        $this->post(route('contacts.store', $client), [
            'name' => 'María Luna', 'photo' => UploadedFile::fake()->image('maria.jpg'),
        ])->assertSessionHasErrors('photo_authorized');
        $this->assertDatabaseCount('contacts', 0);
        $this->post(route('contacts.store', $client), [
            'name' => 'María Luna', 'photo' => UploadedFile::fake()->image('maria.jpg'), 'photo_authorized' => '1',
        ])->assertSessionHasNoErrors();
        $contact = Contact::firstOrFail();
        $path = $contact->photo_path;
        Storage::disk('local')->assertExists($path);
        $this->get(route('contacts.photo', [$client, $contact]))->assertOk();
        $this->put(route('contacts.update', [$client, $contact]), ['name' => 'María Luna', 'role' => 'Dirección'])
            ->assertSessionHasNoErrors()->assertRedirect(route('clients.show', $client));
        $this->assertSame($path, $contact->fresh()->photo_path);
        $this->get(route('contacts.edit', [$client, $contact]))->assertOk()->assertSee('Dirección');
        $this->get(route('clients.show', $client))->assertOk()->assertDontSee('Nos falta conocer su rostro');
        $this->delete(route('contacts.destroy', [$client, $contact]))->assertRedirect(route('clients.show', $client));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_private_clients_contacts_and_images_are_inaccessible_to_other_accounts(): void
    {
        $client = Client::factory()->create();
        $contact = Contact::factory()->for($client)->create();
        $this->actingAs(User::factory()->create());
        $this->get(route('clients.index'))->assertOk()->assertDontSee($client->name);
        foreach (['clients.show', 'clients.edit', 'clients.logo', 'contacts.create'] as $route) {
            $this->get(route($route, $client))->assertNotFound();
        }
        foreach (['contacts.edit', 'contacts.photo'] as $route) {
            $this->get(route($route, [$client, $contact]))->assertNotFound();
        }
        $this->put(route('clients.update', $client), ['name' => 'Otro', 'status' => 'active'])->assertNotFound();
        $this->post(route('contacts.store', $client), ['name' => 'Otro'])->assertNotFound();
        $this->put(route('contacts.update', [$client, $contact]), ['name' => 'Otro'])->assertNotFound();
        $this->delete(route('contacts.destroy', [$client, $contact]))->assertNotFound();
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'name' => $contact->name]);
    }

    public function test_contact_routes_cannot_mix_contacts_from_different_clients(): void
    {
        $client = Client::factory()->create();
        $other = Client::factory()->create(['user_id' => $client->user_id]);
        $contact = Contact::factory()->for($other)->create();
        $this->actingAs($client->user);
        $this->get(route('contacts.edit', [$client, $contact]))->assertNotFound();
        $this->put(route('contacts.update', [$client, $contact]), ['name' => 'Otro'])->assertNotFound();
        $this->delete(route('contacts.destroy', [$client, $contact]))->assertNotFound();
    }

    public function test_directory_searches_people_and_filters_paused_relationships(): void
    {
        $client = Client::factory()->create(['name' => 'Bosque compartido', 'status' => 'paused']);
        Contact::factory()->for($client)->create(['name' => 'María Luna']);
        Client::factory()->create(['user_id' => $client->user_id, 'name' => 'Horizonte']);
        $this->actingAs($client->user);
        $this->get(route('clients.index', ['q' => 'María']))->assertOk()->assertSee('Bosque compartido')->assertDontSee('Horizonte');
        $this->get(route('clients.index', ['status' => 'active']))->assertOk()->assertSee('Horizonte')->assertDontSee('Bosque compartido');
        $this->get(route('clients.index', ['status' => 'paused']))->assertOk()->assertSee('Bosque compartido')->assertDontSee('Horizonte');
        $this->get(route('clients.index', ['q' => 'Sin coincidencias']))->assertOk()->assertSee('Busquemos por otro camino.');
    }

    public function test_forms_reject_invalid_images_and_unsafe_website_links(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($client->user);
        $this->post(route('clients.store'), ['name' => 'Bosque', 'status' => 'active', 'website' => 'javascript:alert(1)', 'logo' => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml')])->assertSessionHasErrors(['website', 'logo']);
        $this->post(route('contacts.store', $client), ['name' => 'María', 'email' => 'incorrecto', 'photo' => UploadedFile::fake()->image('grande.jpg')->size(4096), 'photo_authorized' => '1'])->assertSessionHasErrors(['email', 'photo']);
        $this->post(route('contacts.store', $client), [])->assertSessionHasErrors('name');
        $this->post(route('clients.store'), ['status' => 'desconocido'])->assertSessionHasErrors(['name', 'status']);
    }

    public function test_sidebar_only_offers_overview_and_clients(): void
    {
        $this->actingAs(User::factory()->create())->get(route('clients.index'))
            ->assertOk()->assertSee('Panorama general')->assertSee('Clientes')
            ->assertDontSee('data-projects-link', false)->assertDontSee('team-dialog')->assertDontSee('href="#activity"', false);
        $this->get(route('clients.create'))->assertOk()->assertSee('Conozcamos a quien se suma al camino.');
    }
}
