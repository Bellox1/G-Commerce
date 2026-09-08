<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        $this->authorizeModule('clients');
        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->get();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $clients]);
        }

        return view('clients.index', compact('clients'));
    }

    public function show(Request $request, Client $client)
    {
        $this->authorizeModule('clients');
        $this->authorizeTenant($client);

        $client->load([
            'dettes' => fn ($q) => $q->latest(),
            'ventes' => fn ($q) => $q->latest()->limit(20),
        ]);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'client' => $client,
                    'dettes' => $client->dettes,
                    'ventes' => $client->ventes,
                ],
            ]);
        }

        return view('clients.show', compact('client'));
    }

    public function create()
    {
        $this->authorizeModule('clients');
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $this->authorizeModule('clients');
        $request->validate([
            'nom'       => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'adresse'   => 'nullable|string',
            'limite_credit' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();

        $client = Client::create(array_merge($request->all(), [
            'tenant_id' => $user->tenant_id,
        ]));

        return $this->smartResponse('clients.index', 'Client créé avec succès.', ['client' => $client]);
    }

    public function edit(Client $client)
    {
        $this->authorizeModule('clients');
        $this->authorizeTenant($client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeModule('clients');
        $this->authorizeTenant($client);

        $request->validate([
            'nom'       => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'adresse'   => 'nullable|string',
            'limite_credit' => 'nullable|numeric|min:0',
        ]);

        $client->update($request->all());

        return $this->smartResponse('clients.index', 'Client mis à jour avec succès.');
    }

    public function destroy(Client $client)
    {
        $this->authorizeModule('clients');
        $this->authorizeTenant($client);

        \Illuminate\Support\Facades\DB::transaction(function () use ($client) {
            \App\Models\Vente::where('client_id', $client->id)->update(['client_id' => null]);
            \App\Models\Dette::where('client_id', $client->id)->update(['client_id' => null]);
            $client->delete();
        });

        return $this->smartResponse('clients.index', 'Client supprimé avec succès.');
    }

    private function authorizeTenant(Client $client)
    {
        if ($client->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée sur ce client.');
        }
    }
}
