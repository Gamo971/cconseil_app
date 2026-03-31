<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'raison_sociale',
        'nom_contact',
        'email',
        'telephone',
        'adresse',
        'siret',
        'forme_juridique',
        'annee_creation',
        'type_activite',
        'secteur',
        'statut',
        'notes',
    ];

    protected $casts = [
        'annee_creation' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function financialData(): HasMany
    {
        return $this->hasMany(FinancialData::class)->orderBy('annee', 'desc')->orderBy('mois', 'desc');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(Kpi::class)->orderBy('annee', 'desc')->orderBy('mois', 'desc');
    }

    // ─── Accesseurs / Helpers ─────────────────────────────────────────────────

    /** Mission active en cours */
    public function missionActive(): ?Mission
    {
        return $this->missions()
            ->whereNotIn('statut', ['termine'])
            ->latest('date_debut')
            ->first();
    }

    /** Derniers KPIs calculés */
    public function latestKpi(): ?Kpi
    {
        return $this->kpis()->first();
    }

    /** Dernières données financières */
    public function latestFinancialData(): ?FinancialData
    {
        return $this->financialData()->first();
    }

    /** Badge couleur statut */
    public function statutBadge(): string
    {
        return match($this->statut) {
            'actif'    => 'bg-green-100 text-green-800',
            'prospect' => 'bg-blue-100 text-blue-800',
            'en_pause' => 'bg-yellow-100 text-yellow-800',
            'termine'  => 'bg-gray-100 text-gray-800',
            default    => 'bg-gray-100 text-gray-800',
        };
    }

    /** Libellé statut en français */
    public function statutLabel(): string
    {
        return match($this->statut) {
            'actif'    => 'Actif',
            'prospect' => 'Prospect',
            'en_pause' => 'En pause',
            'termine'  => 'Terminé',
            default    => ucfirst($this->statut),
        };
    }

    /** Libellé type activité */
    public function typeActiviteLabel(): string
    {
        return match($this->type_activite) {
            'service'    => 'Service',
            'negoce'     => 'Négoce',
            'production' => 'Production',
            'mixte'      => 'Mixte',
            default      => ucfirst($this->type_activite),
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeEnAlerte($query)
    {
        return $query->whereHas('kpis', fn($q) => $q->where('alerte', 'rouge'));
    }
}
