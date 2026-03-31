<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'financial_data_id',
        'annee',
        'mois',
        'seuil_rentabilite',
        'taux_marge_brute',
        'marge_brute',
        'ebe',
        'taux_ebe',
        'caf',
        'tresorerie_nette',
        'jours_tresorerie',
        'ca_par_salarie',
        'productivite_salariale',
        'panier_moyen',
        'nombre_clients',
        'taux_remplissage',
        'ca_par_cabine_jour',
        'alerte',
        'analyse_ia',
    ];

    protected $casts = [
        'annee'                  => 'integer',
        'mois'                   => 'integer',
        'seuil_rentabilite'      => 'decimal:2',
        'taux_marge_brute'       => 'decimal:2',
        'marge_brute'            => 'decimal:2',
        'ebe'                    => 'decimal:2',
        'taux_ebe'               => 'decimal:2',
        'caf'                    => 'decimal:2',
        'tresorerie_nette'       => 'decimal:2',
        'jours_tresorerie'       => 'integer',
        'ca_par_salarie'         => 'decimal:2',
        'productivite_salariale' => 'decimal:2',
        'panier_moyen'           => 'decimal:2',
        'nombre_clients'         => 'integer',
        'taux_remplissage'       => 'decimal:2',
        'ca_par_cabine_jour'     => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function financialData(): BelongsTo
    {
        return $this->belongsTo(FinancialData::class);
    }

    // ─── Accesseurs / Helpers ─────────────────────────────────────────────────

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

    /** Dot coloré pour le tableau de bord */
    public function alerteDot(): string
    {
        return match($this->alerte) {
            'vert'   => 'bg-green-500',
            'orange' => 'bg-orange-500',
            'rouge'  => 'bg-red-500',
            default  => 'bg-gray-400',
        };
    }

    /** Libellé de la période */
    public function periodeLabel(): string
    {
        $mois_noms = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aoû',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc',
        ];

        if ($this->mois) {
            return ($mois_noms[$this->mois] ?? $this->mois) . ' ' . $this->annee;
        }

        return 'Annuel ' . $this->annee;
    }

    /** Le CA couvre-t-il le seuil de rentabilité ? */
    public function estRentable(): bool
    {
        $fd = $this->financialData;
        if (! $fd || ! $this->seuil_rentabilite) return false;
        return (float)$fd->ca >= (float)$this->seuil_rentabilite;
    }

    /** Formatage monétaire */
    public static function formatEur(?float $val): string
    {
        if ($val === null) return '—';
        return number_format($val, 0, ',', ' ') . ' €';
    }

    /** Formatage pourcentage */
    public static function formatPct(?float $val): string
    {
        if ($val === null) return '—';
        return number_format($val, 1, ',', ' ') . ' %';
    }
}
