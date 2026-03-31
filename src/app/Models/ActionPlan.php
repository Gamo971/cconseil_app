<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ActionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'objectif',
        'description',
        'kpi_cible',
        'valeur_cible',
        'valeur_actuelle',
        'unite',
        'impact_estime_eur',
        'responsable',
        'date_limite',
        'date_realisation',
        'statut',
        'alerte',
        'priorite',
        'notes',
    ];

    protected $casts = [
        'date_limite'       => 'date',
        'date_realisation'  => 'date',
        'valeur_cible'      => 'decimal:2',
        'valeur_actuelle'   => 'decimal:2',
        'impact_estime_eur' => 'decimal:2',
        'priorite'          => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Calcule automatiquement le statut d'alerte */
    public function refreshAlerte(): string
    {
        if ($this->statut === 'termine') return 'vert';

        $joursRestants = Carbon::now()->diffInDays($this->date_limite, false);

        if ($joursRestants < 0) return 'rouge';      // En retard
        if ($joursRestants <= 7) return 'orange';    // Moins d'1 semaine
        return 'vert';
    }

    /** Badge couleur alerte */
    public function alerteBadge(): string
    {
        return match($this->alerte) {
            'vert'   => 'bg-green-100 text-green-800',
            'orange' => 'bg-orange-100 text-orange-800',
            'rouge'  => 'bg-red-100 text-red-800',
            default  => 'bg-gray-100 text-gray-800',
        };
    }

    /** Libellé statut */
    public function statutLabel(): string
    {
        return match($this->statut) {
            'non_commence' => 'Non commencé',
            'en_cours'     => 'En cours',
            'termine'      => 'Terminé',
            'en_retard'    => 'En retard',
            default        => ucfirst($this->statut),
        };
    }

    /** Avancement en % basé sur valeur actuelle / cible */
    public function avancementPourcent(): float
    {
        if ($this->statut === 'termine') return 100;
        if (! $this->valeur_cible || $this->valeur_cible == 0) return 0;
        if (! $this->valeur_actuelle) return 0;

        return min(100, round(($this->valeur_actuelle / $this->valeur_cible) * 100, 1));
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeEnRetard($query)
    {
        return $query->where('date_limite', '<', now())
                     ->whereNotIn('statut', ['termine']);
    }

    public function scopeHautePriorite($query)
    {
        return $query->where('priorite', 1);
    }
}
