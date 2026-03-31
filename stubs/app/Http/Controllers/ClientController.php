<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::where('user_id', auth()->id())
            ->with(['kpis' => fn($q) => $q->latest('annee')->latest('mois')->limit(1)])
            ->withCount(['missions' => fn($q) => $q->whereNotIn('statut', ['termine'])])
            ->orderBy('statut')
            ->orderBy('raison_sociale')
            ->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'raison_sociale'  => 'required|string|max:255',
            'nom_contact'     => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'telephone'       => 'nullable|string|max:20',
            'adresse'         => 'nullable|string|max:500',
            'siret'           => 'nullable|string|max:14',
            'forme_juridique' => 'nullable|string|max:50',
            'annee_creation'  => 'nullable|integer|min:1900|max:' . date('Y'),
            'type_activite'   => 'required|in:service,negoce,production,mixte',
            'secteur'         => 'required|string|max:100',
            'statut'          => 'required|in:prospect,actif,en_pause,termine',
            'notes'           => 'nullable|string',
        ]);

        $client = Client::create(array_merge($validated, ['user_id' => auth()->id()]));

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Client « {$client->raison_sociale} » créé avec succès.");
    }

    public function show(Client $client): View
    {
        $this->authorize('view', $client);

        $client->load([
            'missions' => fn($q) => $q->withCount('actionPlans')->latest('date_debut'),
            'kpis'     => fn($q) => $q->latest('annee')->latest('mois')->limit(6),
            'financialData' => fn($q) => $q->latest('annee')->latest('mois')->limit(6),
        ]);

        $missionActive = $client->missionActive();
        $latestKpi     = $client->latestKpi();
        $latestFinancialData = $client->latestFinancialData();

        return view('clients.show', compact('client', 'missionActive', 'latestKpi', 'latestFinancialData'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'raison_sociale'  => 'required|string|max:255',
            'nom_contact'     => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'telephone'       => 'nullable|string|max:20',
            'adresse'         => 'nullable|string|max:500',
            'siret'           => 'nullable|string|max:14',
            'forme_juridique' => 'nullable|string|max:50',
            'annee_creation'  => 'nullable|integer|min:1900|max:' . date('Y'),
            'type_activite'   => 'required|in:service,negoce,production,mixte',
            'secteur'         => 'required|string|max:100',
            'statut'          => 'required|in:prospect,actif,en_pause,termine',
            'notes'           => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Fiche client mise à jour.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', "Client « {$client->raison_sociale} » supprimé.");
    }
}
