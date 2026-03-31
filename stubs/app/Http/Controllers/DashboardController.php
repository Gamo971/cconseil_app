<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Mission;
use App\Models\ActionPlan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ── Statistiques globales portefeuille ───────────────────────────────
        $stats = [
            'clients_actifs'      => Client::where('user_id', $user->id)->where('statut', 'actif')->count(),
            'clients_total'       => Client::where('user_id', $user->id)->count(),
            'missions_en_cours'   => Mission::whereHas('client', fn($q) => $q->where('user_id', $user->id))
                                        ->whereNotIn('statut', ['termine'])->count(),
            'actions_en_retard'   => ActionPlan::whereHas('mission.client', fn($q) => $q->where('user_id', $user->id))
                                        ->where('date_limite', '<', now())
                                        ->whereNotIn('statut', ['termine'])->count(),
        ];

        // ── Clients en alerte rouge ───────────────────────────────────────────
        $clientsEnAlerte = Client::where('user_id', $user->id)
            ->where('statut', 'actif')
            ->whereHas('kpis', fn($q) => $q->where('alerte', 'rouge'))
            ->with(['kpis' => fn($q) => $q->latest('annee')->latest('mois')->limit(1)])
            ->limit(5)
            ->get();

        // ── Missions actives avec leur progression ────────────────────────────
        $missionsActives = Mission::whereHas('client', fn($q) => $q->where('user_id', $user->id))
            ->whereNotIn('statut', ['termine'])
            ->with(['client', 'actionPlans'])
            ->orderBy('date_fin')
            ->limit(6)
            ->get();

        // ── Actions urgentes (dues dans les 7 prochains jours) ────────────────
        $actionsUrgentes = ActionPlan::whereHas('mission.client', fn($q) => $q->where('user_id', $user->id))
            ->where('date_limite', '<=', now()->addDays(7))
            ->whereNotIn('statut', ['termine'])
            ->with(['mission.client'])
            ->orderBy('date_limite')
            ->limit(5)
            ->get();

        // ── Répartition par statut d'alerte ──────────────────────────────────
        $repartitionAlertes = [
            'vert'   => Client::where('user_id', $user->id)
                            ->whereHas('kpis', fn($q) => $q->where('alerte', 'vert')
                                ->whereRaw('(annee, mois) IN (SELECT MAX(annee), mois FROM kpis WHERE client_id = clients.id GROUP BY client_id)'))
                            ->count(),
            'orange' => Client::where('user_id', $user->id)
                            ->whereHas('kpis', fn($q) => $q->where('alerte', 'orange'))
                            ->count(),
            'rouge'  => $clientsEnAlerte->count(),
        ];

        return view('dashboard', compact(
            'stats',
            'clientsEnAlerte',
            'missionsActives',
            'actionsUrgentes',
            'repartitionAlertes'
        ));
    }
}
