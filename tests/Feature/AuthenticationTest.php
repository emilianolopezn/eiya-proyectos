<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Vite::partialMock()->shouldReceive('fonts')->andReturn(new HtmlString(''));
    }

    public function test_guests_can_open_login_but_cannot_access_dashboard(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Cada transformación');
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_seeded_administrator_can_log_in_and_view_demo_dashboard(): void
    {
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'admin@eiya.local',
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'admin@eiya.local')->firstOrFail());
        $this->get(route('dashboard'))->assertOk()
            ->assertSee('Hoja de ruta estratégica')
            ->assertSee('Vista de demostración')
            ->assertSee('Sin conexión a proyectos reales');
        $this->get(route('login'))->assertRedirect(route('dashboard'));
    }

    public function test_invalid_credentials_are_rejected_without_retaining_password(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull(session()->getOldInput('password'));
    }

    public function test_login_validates_required_fields(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
        $this->post(route('login.store'), ['email' => ['invalid'], 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_is_normalized_before_authentication(): void
    {
        $this->seed();

        $this->post(route('login.store'), [
            'email' => '  ADMIN@EIYA.LOCAL ',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $this->seed();
        $key = 'admin@eiya.local|127.0.0.1';
        $this->freezeTime();
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'admin@eiya.local',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'admin@eiya.local',
            'password' => 'password',
        ])->assertSessionHasErrors(['email' => 'Demasiados intentos. Inténtalo de nuevo en 60 segundos.']);

        $this->assertGuest();
        RateLimiter::clear($key);
    }

    public function test_logout_invalidates_access_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_administrator_seeder_is_repeatable_and_hashes_password(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $user = User::where('email', 'admin@eiya.local')->firstOrFail();
        $this->assertSame('Administrador', $user->name);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotSame('password', $user->password);
    }
}
