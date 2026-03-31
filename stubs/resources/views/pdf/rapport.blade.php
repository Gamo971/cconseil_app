<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de consultation — {{ $client->raison_sociale }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
            background: #fff;
        }

        /* ── En-tête ---------------------------------------------- */
        .header {
            background: #312e81;
            color: white;
            padding: 24px 32px;
            margin-bottom: 0;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header .subtitle { font-size: 11px; opacity: 0.8; margin-top: 2px; }
        .header .meta { text-align: right; font-size: 10px; opacity: 0.75; }
        .header-client {
            background: rgba(255,255,255,0.12);
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 12px;
        }
        .header-client-name { font-size: 14px; font-weight: bold; }
        .header-client-detail { font-size: 10px; opacity: 0.85; margin-top: 2px; }

        /* ── Alerte badge ----------------------------------------- */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-vert   { background: #d1fae5; color: #065f46; }
        .badge-orange { background: #ffedd5; color: #9a3412; }
        .badge-rouge  { background: #fee2e2; color: #991b1b; }

        /* ── Sections --------------------------------------------- */
        .section {
            padding: 20px 32px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section:last-child { border-bottom: none; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #312e81;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 4px;
            border-bottom: 2px solid #e0e7ff;
        }

        /* ── Tableau KPIs ----------------------------------------- */
        .kpi-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 4px;
        }
        .kpi-card {
            flex: 1;
            min-width: 120px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 10px;
        }
        .kpi-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; }
        .kpi-value { font-size: 13px; font-weight: bold; color: #111827; margin-top: 2px; }
        .kpi-value.rouge { color: #dc2626; }
        .kpi-value.orange { color: #ea580c; }
        .kpi-value.vert   { color: #16a34a; }

        /* ── Analyse IA ------------------------------------------- */
        .analyse-block {
            background: #f0f4ff;
            border-left: 3px solid #4f46e5;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }
        .analyse-block h4 { font-size: 10px; font-weight: bold; color: #4338ca; margin-bottom: 4px; }
        .analyse-block p  { font-size: 10px; color: #374151; line-height: 1.6; }

        /* ── Plan d'action --------------------------------------- */
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #f3f4f6;
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            font-size: 10px;
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }
        .statut-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
        }
        .statut-a_faire    { background: #e5e7eb; color: #374151; }
        .statut-en_cours   { background: #dbeafe; color: #1e40af; }
        .statut-termine    { background: #d1fae5; color: #065f46; }
        .statut-en_retard  { background: #fee2e2; color: #991b1b; }

        /* ── Pied de page ---------------------------------------- */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0; right: 0;
            padding: 8px 32px;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .synthese-box {
            background: #ede9fe;
            border: 1px solid #c4b5fd;
            border-radius: 6px;
            padding: 10px 14px;
        }
        .synthese-box p { font-size: 10px; font-style: italic; color: #4c1d95; line-height: 1.6; }
    </style>
</head>
<body>

    {{-- Pied de page (rendu par DomPDF) --}}
    <div class="footer">
        <span>{{ $client->raison_sociale }} — Compte rendu de consultation</span>
        <span>Généré le {{ now()->format('d/m/Y') }} · {{ $consultant->name }}</span>
    </div>

    {{-- En-tête --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="subtitle">Compte rendu de consultation</div>
                <h1>Rapport d'analyse financière</h1>
            </div>
            <div class="meta">
                Période : {{ $periode }}<br>
                Date : {{ now()->format('d/m/Y') }}<br>
                Consultant : {{ $consultant->name }}
            </div>
        </div>
        <div class="header-client">
            <div class="header-client-name">{{ $client->raison_sociale }}</div>
            <div class="header-client-detail">
                {{ $client->secteur ?? '' }}{{ $client->secteur && $client->type_activite ? ' · ' : '' }}{{ $client->typeActiviteLabel() }}
                @if($client->siret) · SIRET {{ $client->siret }}@endif
            </div>
        </div>
    </div>

    {{-- Section 1 : Indicateurs financiers --}}
    <div class="section">
        <div class="section-title">
            Indicateurs financiers — {{ $periode }}
            &nbsp;
            <span class="badge badge-{{ $latestKpi->alerte ?? 'vert' }}">
                Alerte : {{ strtoupper($latestKpi->alerte ?? 'NC') }}
            </span>
        </div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Chiffre d'affaires</div>
                <div class="kpi-value">{{ \App\Models\Kpi::formatEur((float)$fd->ca) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Seuil de rentabilité</div>
                <div class="kpi-value {{ $latestKpi->seuil_rentabilite && $fd->ca < $latestKpi->seuil_rentabilite ? 'rouge' : '' }}">
                    {{ \App\Models\Kpi::formatEur($latestKpi->seuil_rentabilite) }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Taux de marge brute</div>
                <div class="kpi-value {{ ($latestKpi->taux_marge_brute ?? 100) < 20 ? 'orange' : '' }}">
                    {{ \App\Models\Kpi::formatPct($latestKpi->taux_marge_brute) }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">EBE</div>
                <div class="kpi-value {{ ($latestKpi->ebe ?? 1) < 0 ? 'rouge' : 'vert' }}">
                    {{ \App\Models\Kpi::formatEur($latestKpi->ebe) }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Trésorerie nette</div>
                <div class="kpi-value {{ ($latestKpi->tresorerie_nette ?? 1) < 0 ? 'rouge' : '' }}">
                    {{ \App\Models\Kpi::formatEur($latestKpi->tresorerie_nette) }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Jours de trésorerie</div>
                <div class="kpi-value {{ ($latestKpi->jours_tresorerie ?? 999) < 15 ? 'orange' : '' }}">
                    {{ $latestKpi->jours_tresorerie !== null ? $latestKpi->jours_tresorerie . ' j' : '—' }}
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">CAF</div>
                <div class="kpi-value">{{ \App\Models\Kpi::formatEur($latestKpi->caf) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">CA / salarié</div>
                <div class="kpi-value">{{ \App\Models\Kpi::formatEur($latestKpi->ca_par_salarie) }}</div>
            </div>
        </div>
    </div>

    {{-- Section 2 : Synthèse exécutive --}}
    @if(!empty($analyse['synthese']))
    <div class="section">
        <div class="section-title">Synthèse exécutive</div>
        <div class="synthese-box">
            <p>{{ $analyse['synthese'] }}</p>
        </div>
    </div>
    @endif

    {{-- Section 3 : Analyse IA détaillée --}}
    @if(!empty($analyse['diagnostic']) || !empty($analyse['recommandations']))
    <div class="section">
        <div class="section-title">Analyse contextuelle</div>

        @if(!empty($analyse['diagnostic']))
        <div class="analyse-block">
            <h4>Diagnostic</h4>
            <p>{{ $analyse['diagnostic'] }}</p>
        </div>
        @endif

        @if(!empty($analyse['recommandations']))
        <div class="analyse-block">
            <h4>Recommandations prioritaires (90 jours)</h4>
            <p style="white-space: pre-line;">{{ $analyse['recommandations'] }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Section 4 : Mission en cours --}}
    @if($mission)
    <div class="section">
        <div class="section-title">Mission en cours — {{ $mission->phaseLabel() }}</div>
        <table style="margin-bottom:8px">
            <tr>
                <td style="width:50%; font-weight:bold; color:#374151;">Objectif global</td>
                <td>{{ $mission->objectif_global }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; color:#374151;">Période</td>
                <td>{{ $mission->date_debut?->format('d/m/Y') }} → {{ $mission->date_fin?->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; color:#374151;">Honoraires HT</td>
                <td>{{ \App\Models\Kpi::formatEur((float)$mission->honoraires_ht) }}</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Section 5 : Plan d'action --}}
    @if($actions->count() > 0)
    <div class="section">
        <div class="section-title">Plan d'action</div>
        <table>
            <thead>
                <tr>
                    <th style="width:40%">Action</th>
                    <th style="width:15%">Responsable</th>
                    <th style="width:15%">Échéance</th>
                    <th style="width:10%">Priorité</th>
                    <th style="width:15%">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($actions as $action)
                <tr>
                    <td>{{ $action->titre }}</td>
                    <td>{{ $action->responsable ?? '—' }}</td>
                    <td>{{ $action->date_limite ? $action->date_limite->format('d/m/Y') : '—' }}</td>
                    <td>
                        @if($action->priorite == 1) 🔴 Haute
                        @elseif($action->priorite == 2) 🟠 Moyenne
                        @else 🟢 Basse
                        @endif
                    </td>
                    <td>
                        <span class="statut-badge statut-{{ $action->statut }}">
                            {{ ucfirst(str_replace('_', ' ', $action->statut)) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</body>
</html>
