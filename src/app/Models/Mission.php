<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'type_mission',
        'objectif_global',
        'date_debut',
        'date_fin',
        'phase_courante',
        'statut',
        'honoraires_ht',
        'mode_facturation',
        'notes',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin'        => 'date',
        'honoraires_ht'   => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(ActionPlan::class)->orderBy('priorite')->orderBy('date_limite');
    }

    public function meetingReports(): HasMany
    {
        return $this->hasMany(MeetingReport::class)->orderBy('date_reunion', 'desc');
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Jours restants avant fin de mission */
    public function joursRestants(): int
    {
        return max(0, Carbon::now()->diffInDays($this->date_fin, false));
    }

    /** Avancement en % selon les phases */
    public function avancementPourcent(): int
    {
        return match($this->phase_courante) {
            'phase_1_diagnostic'   => 10,
            'phase_2_plan_action'  => 35,
            'phase_3_pilotage'     => 65,
            'phase_4_optimisation' => 90,
            'terminee'             => 100,
            default                => 0,
        };
    }

    /** Libellé de la phase courante */
    public function phaseLabel(): string
    {
        return match($this->phase_courante) {
            'phase_1_diagnostic'   => 'Phase 1 — Diagnostic',
            'phase_2_plan_action'  => 'Phase 2 — Plan d\'action',
            'phase_3_pilotage'     => 'Phase 3 — Pilotage',
            'phase_4_optimisation' => 'Phase 4 — Optimisation',
            'terminee'             => 'Terminée',
            default                => ucfirst($this->phase_courante),
        };
    }

    /** Badge couleur statut */
    public function statutBadge(): string
    {
        return match($this->statut) {
            'vert'    => 'bg-green-100 text-green-800',
            'orange'  => 'bg-orange-100 text-orange-800',
            'rouge'   => 'bg-red-100 text-red-800',
            'termine' => 'bg-gray-100 text-gray-800',
            default   => 'bg-gray-100 text-gray-800',
        };
    }

    /** Badge couleur statut (point coloré) */
    public function statutDot(): string
    {
        return match($this->statut) {
            'vert'    => 'bg-green-500',
            'orange'  => 'bg-orange-500',
            'rouge'   => 'bg-red-500',
            'termine' => 'bg-gray-400',
            default   => 'bg-gray-400',
        };
    }

    /** Actions du plan en retard */
    public function actionsEnRetard(): int
    {
        return $this->actionPlans()
            ->where('statut', 'en_retard')
            ->orWhere(function ($q) {
                $q->where('date_limite', '<', now())
                  ->whereNotIn('statut', ['termine']);
            })
            ->count();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeEnCours($query)
    {
        return $query->whereNotIn('statut', ['termine']);
    }

    public function scopeEnAlerte($query)
    {
        return $query->whereIn('statut', ['rouge', 'orange']);
    }
}
