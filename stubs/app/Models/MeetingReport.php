<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'titre',
        'date_reunion',
        'lieu',
        'participants',
        'situation_actuelle',
        'indicateurs_cles',
        'problemes_identifies',
        'decisions_prises',
        'actions_a_realiser',
        'responsables',
        'echeances',
        'impact_attendu',
        'notes_brutes',
        'genere_par_ia',
        'fichier_pdf',
    ];

    protected $casts = [
        'date_reunion'    => 'date',
        'indicateurs_cles' => 'array',
        'genere_par_ia'   => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────────

    /** Le compte rendu a-t-il un PDF généré ? */
    public function hasPdf(): bool
    {
        return ! empty($this->fichier_pdf);
    }

    /** Résumé court pour les listes */
    public function resumeCourt(): string
    {
        $text = $this->decisions_prises ?? $this->situation_actuelle ?? '';
        return mb_strlen($text) > 150 ? mb_substr($text, 0, 150) . '…' : $text;
    }
}
