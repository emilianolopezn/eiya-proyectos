<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveContactRequest;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ContactController extends Controller
{
    public function create(Request $request, Client $client): View
    {
        $this->checkAccess($request, $client);

        return view('clients.contact-form', ['client' => $client, 'contact' => new Contact]);
    }

    public function store(SaveContactRequest $request, Client $client): RedirectResponse
    {
        $this->checkAccess($request, $client);
        $contact = new Contact;
        $contact->client()->associate($client);
        $this->save($request, $contact);

        return to_route('clients.show', $client)->with('status', 'Una persona más a quien conocer y acompañar. Su contacto está guardado.');
    }

    public function edit(Request $request, Client $client, Contact $contact): View
    {
        $this->checkAccess($request, $client, $contact);

        return view('clients.contact-form', compact('client', 'contact'));
    }

    public function update(SaveContactRequest $request, Client $client, Contact $contact): RedirectResponse
    {
        $this->checkAccess($request, $client, $contact);
        $this->save($request, $contact);

        return to_route('clients.show', $client)->with('status', 'Contacto actualizado. Cada detalle nos ayuda a estar más cerca.');
    }

    public function destroy(Request $request, Client $client, Contact $contact): RedirectResponse
    {
        $this->checkAccess($request, $client, $contact);
        $path = $contact->photo_path;
        $contact->delete();
        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return to_route('clients.show', $client)->with('status', 'El contacto se retiró de esta lista.');
    }

    public function photo(Request $request, Client $client, Contact $contact): StreamedResponse
    {
        $this->checkAccess($request, $client, $contact);
        abort_unless($contact->photo_path && Storage::disk('local')->exists($contact->photo_path), 404);

        return Storage::disk('local')->response($contact->photo_path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function checkAccess(Request $request, Client $client, ?Contact $contact = null): void
    {
        abort_unless($client->user_id === $request->user()->id, 404);
        abort_if($contact && $contact->client_id !== $client->id, 404);
    }

    private function save(SaveContactRequest $request, Contact $contact): void
    {
        $oldPath = $contact->photo_path;
        $newPath = $request->file('photo')?->store('clients/contacts', 'local');
        abort_if($newPath === false, 500, 'No pudimos guardar la foto. Inténtalo de nuevo.');
        try {
            $contact->fill($request->safe()->except(['photo', 'photo_authorized']));
            if ($newPath) {
                $contact->photo_path = $newPath;
            }
            $contact->save();
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }
        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }
}
