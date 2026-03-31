<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinancialData extends Model
{
    use HasFactory;

    protected $table = 'financial_data';

    protected $fillable = [
        'client_id',
        'annee',
        'mois',
        'ca',
        'achats_marchandises',
        'autres_achats',
        'charges_fixes',
        'charges_variables',
        'masse_salariale_brute',
        'charges_patronales',
        'nombre_salaries',
        'dette_totale',
        'dette_fournisseurs',
        'dette_fiscale_sociale',
        'investissements',
        'amortissements',
        'tresorerie_debut',
        'tresorerie_fin',
        'source',
        'notes',
    ];

    protected $casts = [
        'annee'                  => 'integer',
        'mois'                   => 'integer',
        'ca'                     => 'decimal:2',
        'achats_marchandises'    => 'decimal:2',
        'autres_achats'          => 'decimal:2',
        'charges_fixes'          => 'decimal:2',
        'charges_variables'      => 'decimal:2',
        'masse_salariale_brute'  => 'decimal:2',
        'charges_patronales'     => 'decimal:2',
        'nombre_salaries'        => 'integer',
        'dette_totale'           => 'decimal:2',
        'dette_fournisseurs'     => 'decimal:2',
        'dette_fiscale_sociale'  => 'decimal:2',
        'investissements'        => 'decimal:2',
        'amortissements'         => 'decimal:2',
        'tresorerie_debut'       => 'decimal:2',
        'tresorerie_fin'         => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function kpi(): HasOne
    {
        return $this->hasOne(Kpi::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Libellé de la période */
    public function periodeLabel(): string
    {
        $mois_noms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        if ($this->mois) {
            return ($mois_noms[$this->mois] ?? $this->mois) . ' ' . $this->annee;
        }

        return 'Annuel ' . $this->annee;
    }

    /** Total charges (fixes + variables + masse salariale) */
    public function totalCharges(): float
    {
        return (float)($this->charges_fixes ?? 0)
             + (float)($this->charges_variables ?? 0)
             + (float)($this->masse_salariale_brute ?? 0)
             + (float)($this->charges_patronales ?? 0);
    }

    /** Masse salariale totale (brut + patronales) */
    public function masseSalarialeTotal(): float
    {
        return (float)($this->masse_salariale_brute ?? 0)
             + (float)($this->charges_patronales ?? 0);
    }

    /** Libellé source */
    public function sourceLabel(): string
    {
        return match($this->source) {
            'saisie_manuelle' => 'Saisie manuelle',
            'import_csv'      => 'Import CSV',
            'import_api'      => 'Import API',
            default           => ucfirst($this->source),
        };
    }
}
