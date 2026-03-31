<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Mission;
use App\Models\ActionPlan;

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

        // ── Clients en alerte rouge (dernier KPI = rouge) ─────────────────────
        $clientsEnAlerte = Client::where('user_id', $user->id)
            ->where('statut', 'actif')
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('kpis as k')
                    ->whereColumn('k.client_id', 'clients.id')
                    ->where('k.alerte', 'rouge')
                    ->whereRaw('(k.annee, k.mois) = (
                        SELECT k2.annee, k2.mois FROM kpis AS k2
                        WHERE k2.client_id = k.client_id
                        ORDER BY k2.annee DESC, k2.mois DESC
                        LIMIT 1
                    )');
            })
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

        // ── Répartition par statut d'alerte (dernier KPI par client : année/mois max) ─
        $repartitionAlertes = [
            'vert'   => $this->countClientsByLatestKpiAlert($user->id, 'vert'),
            'orange' => $this->countClientsByLatestKpiAlert($user->id, 'orange'),
            'rouge'  => $this->countClientsByLatestKpiAlert($user->id, 'rouge'),
        ];

        return view('dashboard', compact(
            'stats',
            'clientsEnAlerte',
            'missionsActives',
            'actionsUrgentes',
            'repartitionAlertes'
        ));
    }

    /**
     * Clients dont le KPI le plus récent (annee desc, mois desc) a l’alerte donnée.
     */
    protected function countClientsByLatestKpiAlert(int $userId, string $alerte): int
    {
        return Client::query()
            ->where('user_id', $userId)
            ->whereExists(function ($q) use ($alerte) {
                $q->selectRaw('1')
                    ->from('kpis as k')
                    ->whereColumn('k.client_id', 'clients.id')
                    ->where('k.alerte', $alerte)
                    ->whereRaw('(k.annee, k.mois) = (
                        SELECT k2.annee, k2.mois FROM kpis AS k2
                        WHERE k2.client_id = k.client_id
                        ORDER BY k2.annee DESC, k2.mois DESC
                        LIMIT 1
                    )');
            })
            ->count();
    }
}
