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
        return view('clients.index', compact('clients'));
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

        Client::create(array_merge($request->all(), [
            'tenant_id' => $user->tenant_id,
        ]));

        return redirect()->route('clients.index')->with('success', 'Client créé avec succès.');
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

        return redirect()->route('clients.index')->with('success', 'Client mis à jour avec succès.');
    }

    public function destroy(Client $client)
    {
        $this->authorizeModule('clients');
        $this->authorizeTenant($client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client retiré de la base de données.');
    }

    private function authorizeTenant(Client $client)
    {
        if ($client->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée sur ce client.');
        }
    }
}
