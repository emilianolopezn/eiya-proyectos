<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,paused'],
        ]);
        $query = Client::where('user_id', $request->user()->id);
        $clients = (clone $query)->with('contacts')->orderBy('name')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('industry', 'like', '%'.$term.'%')
                        ->orWhereHas('contacts', fn ($contacts) => $contacts->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->when(in_array($filters['status'] ?? 'all', ['active', 'paused']), fn ($query) => $query->where('status', $filters['status']))
            ->paginate(12)->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'clientCount' => (clone $query)->count(),
            'contactCount' => (clone $query)->withCount('contacts')->get()->sum('contacts_count'),
            'missingPhotos' => (clone $query)->whereHas('contacts', fn ($contacts) => $contacts->whereNull('photo_path'))->count(),
        ]);
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client(['status' => 'active'])]);
    }

    public function store(SaveClientRequest $request): RedirectResponse
    {
        $client = new Client(['user_id' => $request->user()->id]);
        $this->save($request, $client);

        return to_route('clients.show', $client)->with('status', 'Un nuevo camino compartido. Ya puedes sumar a las personas con quienes colaboramos.');
    }

    public function show(Request $request, Client $client): View
    {
        $this->checkAccess($request, $client);

        return view('clients.show', ['client' => $client->load('contacts')]);
    }

    public function edit(Request $request, Client $client): View
    {
        $this->checkAccess($request, $client);

        return view('clients.form', compact('client'));
    }

    public function update(SaveClientRequest $request, Client $client): RedirectResponse
    {
        $this->checkAccess($request, $client);
        $this->save($request, $client);

        return to_route('clients.show', $client)->with('status', 'Gracias por cuidar los detalles. Su información ya está actualizada.');
    }

    public function logo(Request $request, Client $client): StreamedResponse
    {
        $this->checkAccess($request, $client);
        abort_unless($client->logo_path && Storage::disk('local')->exists($client->logo_path), 404);

        return Storage::disk('local')->response($client->logo_path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function checkAccess(Request $request, Client $client): void
    {
        abort_unless($client->user_id === $request->user()->id, 404);
    }

    private function save(SaveClientRequest $request, Client $client): void
    {
        $oldPath = $client->logo_path;
        $newPath = $request->file('logo')?->store('clients/logos', 'local');
        abort_if($newPath === false, 500, 'No pudimos guardar el logo. Inténtalo de nuevo.');
        try {
            DB::transaction(function () use ($request, $client, $newPath): void {
                $client->fill($request->safe()->except('logo'));
                if ($newPath) {
                    $client->logo_path = $newPath;
                }
                $client->save();
            });
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
