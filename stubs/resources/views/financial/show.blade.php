@extends('layouts.app')

@section('title', 'Indicateurs — ' . $financialData->periodeLabel())
@section('subtitle', $financialData->client->raison_sociale)

@section('header-actions')
    <a href="{{ route('financial-data.edit', $financialData) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
        Modifier les données
    </a>
    <a href="{{ route('clients.show', $financialData->client) }}" class="text-sm text-gray-500 hover:text-gray-700">← Client</a>
@endsection

@section('content')

{{-- ── Niveau d'alerte global ──────────────────────────────────────────────── --}}
@php $alerte = $kpiData['alerte'] ?? 'vert'; @endphp
<div class="mb-6 p-4 rounded-xl border
    {{ $alerte === 'rouge' ? 'bg-red-50 border-red-200' : ($alerte === 'orange' ? 'bg-orange-50 border-orange-200' : 'bg-green-50 border-green-200') }}">
    <div class="flex items-center gap-3">
        <div class="w-4 h-4 rounded-full
            {{ $alerte === 'rouge' ? 'bg-red-500' : ($alerte === 'orange' ? 'bg-orange-500' : 'bg-green-500') }}"></div>
        <div>
            <span class="font-semibold text-sm
                {{ $alerte === 'rouge' ? 'text-red-800' : ($alerte === 'orange' ? 'text-orange-800' : 'text-green-800') }}">
                Niveau d'alerte : {{ strtoupper($alerte) }}
            </span>
            @if($commentaire)
                <div class="mt-1 text-sm whitespace-pre-line
                    {{ $alerte === 'rouge' ? 'text-red-700' : ($alerte === 'orange' ? 'text-orange-700' : 'text-green-700') }}">
                    {{ $commentaire }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- ── KPIs principaux ──────────────────────────────────────────────── --}}
    <div class="col-span-2 space-y-5">

        {{-- Rentabilité --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">📊 Rentabilité</h2>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">

                @php
                    $ca = $financialData->ca ?? 0;
                    $seuil = $kpiData['seuil_rentabilite'] ?? null;
                    $margeBrute = $kpiData['marge_brute'] ?? null;
                    $tauxMarge = $kpiData['taux_marge_brute'] ?? null;
                    $ebe = $kpiData['ebe'] ?? null;
                    $tauxEbe = $kpiData['taux_ebe'] ?? null;
                @endphp

                <div class="border-l-4 {{ $seuil && $ca < $seuil ? 'border-red-400' : 'border-green-400' }} pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">CA réalisé</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($ca, 0, ',', ' ') }} €</div>
                </div>

                <div class="border-l-4 {{ $seuil && $ca < $seuil ? 'border-red-400' : 'border-indigo-400' }} pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">Seuil de rentabilité</div>
                    <div class="text-xl font-bold text-gray-900">
                        {{ $seuil ? number_format($seuil, 0, ',', ' ') . ' €' : '—' }}
                    </div>
                    @if($seuil && $ca > 0)
                        @php $ecart = $ca - $seuil; @endphp
                        <div class="text-xs mt-0.5 {{ $ecart >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }} €
                            {{ $ecart >= 0 ? 'au-dessus' : 'en dessous' }} du seuil
                        </div>
                    @endif
                </div>

                <div class="border-l-4 border-gray-200 pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">Marge brute</div>
                    <div class="text-xl font-bold {{ $margeBrute < 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $margeBrute !== null ? number_format($margeBrute, 0, ',', ' ') . ' €' : '—' }}
                    </div>
                </div>

                <div class="border-l-4 border-gray-200 pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">Taux de marge brute</div>
                    <div class="text-xl font-bold {{ ($tauxMarge ?? 100) < 20 ? 'text-orange-600' : 'text-gray-900' }}">
                        {{ $tauxMarge !== null ? number_format($tauxMarge, 1, ',', ' ') . ' %' : '—' }}
                    </div>
                </div>

                <div class="border-l-4 {{ ($ebe ?? 1) < 0 ? 'border-red-400' : 'border-green-400' }} pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">EBE (Excédent Brut d'Expl.)</div>
                    <div class="text-xl font-bold {{ ($ebe ?? 1) < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $ebe !== null ? number_format($ebe, 0, ',', ' ') . ' €' : '—' }}
                    </div>
                </div>

                <div class="border-l-4 border-gray-200 pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">Taux EBE / CA</div>
                    <div class="text-xl font-bold text-gray-900">
                        {{ $tauxEbe !== null ? number_format($tauxEbe, 1, ',', ' ') . ' %' : '—' }}
                    </div>
                </div>

            </div>
        </div>

        {{-- Trésorerie --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">💰 Trésorerie</h2>
            <div class="grid grid-cols-2 gap-x-8 gap-y-4">

                @php
                    $treso = $kpiData['tresorerie_nette'] ?? null;
                    $joursTreso = $kpiData['jours_tresorerie'] ?? null;
                    $caf = $kpiData['caf'] ?? null;
                @endphp

                <div class="border-l-4 {{ ($treso ?? 1) < 0 ? 'border-red-400' : (($treso ?? 1) < 5000 ? 'border-orange-400' : 'border-green-400') }} pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">Trésorerie nette</div>
                    <div class="text-xl font-bold {{ ($treso ?? 1) < 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $treso !== null ? number_format($treso, 0, ',', ' ') . ' €' : '—' }}
                    </div>
                </div>

                <div class="border-l-4 border-gray-200 pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">Autonomie de trésorerie</div>
                    <div class="text-xl font-bold {{ ($joursTreso ?? 100) < 15 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $joursTreso !== null ? $joursTreso . ' jours' : '—' }}
                    </div>
                </div>

                <div class="border-l-4 border-gray-200 pl-4">
                    <div class="text-xs text-gray-500 mb-0.5">CAF (Capacité d'autofinancement)</div>
                    <div class="text-xl font-bold text-gray-900">
                        {{ $caf !== null ? number_format($caf, 0, ',', ' ') . ' €' : '—' }}
                    </div>
                </div>

            </div>
        </div>

        {{-- Productivité --}}
        @if($kpiData['ca_par_salarie'] || $kpiData['productivite_salariale'])
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-5">👥 Productivité salariale</h2>
                <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                    <div class="border-l-4 border-gray-200 pl-4">
                        <div class="text-xs text-gray-500 mb-0.5">CA par salarié</div>
                        <div class="text-xl font-bold text-gray-900">
                            {{ $kpiData['ca_par_salarie'] ? number_format($kpiData['ca_par_salarie'], 0, ',', ' ') . ' €' : '—' }}
                        </div>
                    </div>
                    <div class="border-l-4 border-gray-200 pl-4">
                        <div class="text-xs text-gray-500 mb-0.5">Productivité salariale</div>
                        <div class="text-xl font-bold text-gray-900">
                            {{ $kpiData['productivite_salariale'] ? number_format($kpiData['productivite_salariale'], 1, ',', ' ') . ' %' : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ── Colonne droite : données saisies ─────────────────────────────── --}}
    <div class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Données saisies</h2>
            <div class="space-y-2.5 text-sm">

                @foreach([
                    'CA HT' => $financialData->ca,
                    'Achats marchandises' => $financialData->achats_marchandises,
                    'Autres achats' => $financialData->autres_achats,
                    'Charges fixes' => $financialData->charges_fixes,
                    'Charges variables' => $financialData->charges_variables,
                    'Masse sal. brute' => $financialData->masse_salariale_brute,
                    'Charges patronales' => $financialData->charges_patronales,
                    'Nb salariés' => $financialData->nombre_salaries,
                    'Dette totale' => $financialData->dette_totale,
                    'Trésorerie fin' => $financialData->tresorerie_fin,
                    'Investissements' => $financialData->investissements,
                ] as $label => $val)
                    @if($val !== null)
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-xs">{{ $label }}</span>
                            <span class="text-gray-900 text-xs font-medium">
                                @if($label === 'Nb salariés')
                                    {{ $val }}
                                @else
                                    {{ number_format($val, 0, ',', ' ') }} €
                                @endif
                            </span>
                        </div>
                    @endif
                @endforeach

            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-400">
                Saisie le {{ $financialData->created_at->format('d/m/Y') }}
                · {{ $financialData->sourceLabel() }}
            </div>
        </div>

        {{-- Action Sprint 2 : analyse IA --}}
        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-5">
            <h2 class="text-sm font-semibold text-indigo-900 mb-2">🤖 Analyse IA (Sprint 2)</h2>
            <p class="text-xs text-indigo-700 leading-relaxed">
                La génération d'une analyse contextuelle par Claude sera disponible dans la
                prochaine version. Elle produira : points d'alerte, recommandations hiérarchisées
                et objectifs SMART.
            </p>
        </div>

    </div>
</div>

@endsection
