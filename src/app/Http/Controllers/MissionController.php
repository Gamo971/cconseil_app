<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Mission;
use App\Models\ActionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function index(): View
    {
        $missions = Mission::whereHas('client', fn($q) => $q->where('user_id', auth()->id()))
            ->with(['client'])
            ->withCount(['actionPlans', 'actionPlans as actions_en_retard_count' => fn($q) =>
                $q->where('date_limite', '<', now())->whereNotIn('statut', ['termine'])
            ])
            ->orderByRaw("FIELD(statut, 'rouge', 'orange', 'vert', 'termine')")
            ->paginate(20);

        return view('missions.index', compact('missions'));
    }

    public function create(Request $request): View
    {
        $clients = Client::where('user_id', auth()->id())
            ->whereIn('statut', ['actif', 'prospect'])
            ->orderBy('raison_sociale')
            ->get();

        $clientSelectionne = $request->query('client_id')
            ? Client::find($request->query('client_id'))
            : null;

        return view('missions.create', compact('clients', 'clientSelectionne'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'type_mission'    => 'required|string|max:255',
            'objectif_global' => 'nullable|string',
            'date_debut'      => 'required|date',
            'date_fin'        => 'required|date|after:date_debut',
            'phase_courante'  => 'required|in:phase_1_diagnostic,phase_2_plan_action,phase_3_pilotage,phase_4_optimisation,terminee',
            'statut'          => 'required|in:vert,orange,rouge,termine',
            'honoraires_ht'   => 'nullable|numeric|min:0',
            'mode_facturation' => 'required|in:forfait,mensuel,journalier',
            'notes'           => 'nullable|string',
        ]);

        // Vérifier que le client appartient bien à l'utilisateur
        $client = Client::where('id', $validated['client_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $mission = Mission::create($validated);

        return redirect()
            ->route('missions.show', $mission)
            ->with('success', "Mission créée pour {$client->raison_sociale}.");
    }

    public function show(Mission $mission): View
    {
        $this->authorize('view', $mission);

        $mission->load([
            'client',
            'actionPlans' => fn($q) => $q->orderBy('priorite')->orderBy('date_limite'),
            'meetingReports' => fn($q) => $q->latest('date_reunion'),
        ]);

        return view('missions.show', compact('mission'));
    }

    public function edit(Mission $mission): View
    {
        $this->authorize('update', $mission);

        $clients = Client::where('user_id', auth()->id())
            ->orderBy('raison_sociale')
            ->get();

        return view('missions.edit', compact('mission', 'clients'));
    }

    public function update(Request $request, Mission $mission): RedirectResponse
    {
        $this->authorize('update', $mission);

        $validated = $request->validate([
            'type_mission'    => 'required|string|max:255',
            'objectif_global' => 'nullable|string',
            'date_debut'      => 'required|date',
            'date_fin'        => 'required|date|after:date_debut',
            'phase_courante'  => 'required|in:phase_1_diagnostic,phase_2_plan_action,phase_3_pilotage,phase_4_optimisation,terminee',
            'statut'          => 'required|in:vert,orange,rouge,termine',
            'honoraires_ht'   => 'nullable|numeric|min:0',
            'mode_facturation' => 'required|in:forfait,mensuel,journalier',
            'notes'           => 'nullable|string',
        ]);

        $mission->update($validated);

        return redirect()
            ->route('missions.show', $mission)
            ->with('success', 'Mission mise à jour.');
    }

    // ─── Gestion des actions du plan ─────────────────────────────────────────

    public function storeAction(Request $request, Mission $mission): RedirectResponse
    {
        $this->authorize('update', $mission);

        $validated = $request->validate([
            'objectif'          => 'required|string|max:255',
            'description'       => 'nullable|string',
            'kpi_cible'         => 'required|string|max:100',
            'valeur_cible'      => 'nullable|numeric',
            'unite'             => 'nullable|string|max:20',
            'impact_estime_eur' => 'nullable|numeric|min:0',
            'responsable'       => 'nullable|string|max:100',
            'date_limite'       => 'required|date',
            'priorite'          => 'required|in:1,2,3',
        ]);

        $action = $mission->actionPlans()->create(array_merge($validated, [
            'statut' => 'non_commence',
            'alerte' => 'vert',
        ]));

        // Recalculer l'alerte
        $action->alerte = $action->refreshAlerte();
        $action->save();

        return redirect()
            ->route('missions.show', $mission)
            ->with('success', 'Action ajoutée au plan.');
    }

    public function updateActionStatut(Request $request, Mission $mission, ActionPlan $action): RedirectResponse
    {
        $this->authorize('update', $mission);

        $request->validate([
            'statut' => 'required|in:non_commence,en_cours,termine,en_retard',
        ]);

        $action->update([
            'statut'           => $request->statut,
            'alerte'           => $action->refreshAlerte(),
            'date_realisation' => $request->statut === 'termine' ? now() : null,
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }
}
