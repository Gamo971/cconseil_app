@extends('layouts.app')

@section('title', $client->raison_sociale)
@section('subtitle', $client->secteur . ' · ' . $client->typeActiviteLabel())

@section('header-actions')
    <a href="{{ route('financial-data.create') }}?client_id={{ $client->id }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Saisir données financières
    </a>
    <a href="{{ route('missions.create') }}?client_id={{ $client->id }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle mission
    </a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-6">

    {{-- ── Colonne gauche : infos client ────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Carte statut --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Fiche client</h2>
                <a href="{{ route('clients.edit', $client) }}" class="text-xs text-indigo-600 hover:underline">Modifier</a>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Statut</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $client->statutBadge() }}">
                        {{ $client->statutLabel() }}
                    </span>
                </div>
                @if($client->nom_contact)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Contact</span>
                        <span class="text-gray-900">{{ $client->nom_contact }}</span>
                    </div>
                @endif
                @if($client->telephone)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Téléphone</span>
                        <a href="tel:{{ $client->telephone }}" class="text-indigo-600">{{ $client->telephone }}</a>
                    </div>
                @endif
                @if($client->email)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email</span>
                        <a href="mailto:{{ $client->email }}" class="text-indigo-600 truncate max-w-32">{{ $client->email }}</a>
                    </div>
                @endif
                @if($client->siret)
                    <div class="flex justify-between">
                        <span class="text-gray-500">SIRET</span>
                        <span class="text-gray-900 font-mono text-xs">{{ $client->siret }}</span>
                    </div>
                @endif
                @if($client->forme_juridique)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Forme jur.</span>
                        <span class="text-gray-900">{{ $client->forme_juridique }}</span>
                    </div>
                @endif
                @if($client->annee_creation)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Créée en</span>
                        <span class="text-gray-900">{{ $client->annee_creation }}</span>
                    </div>
                @endif
            </div>

            @if($client->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 font-medium mb-1">Notes</p>
                    <p class="text-xs text-gray-700 leading-relaxed">{{ $client->notes }}</p>
                </div>
            @endif
        </div>

        {{-- KPIs dernière période --}}
        @if($latestKpi)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-900">Indicateurs — {{ $latestKpi->periodeLabel() }}</h2>
                    <div class="w-2 h-2 rounded-full {{ $latestKpi->alerteDot() }}"></div>
                </div>
                <div class="space-y-3 text-sm">
                    @if($latestKpi->seuil_rentabilite)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Seuil rentabilité</span>
                            <span class="font-semibold text-gray-900">{{ \App\Models\Kpi::formatEur((float)$latestKpi->seuil_rentabilite) }}</span>
                        </div>
                    @endif
                    @if($latestKpi->taux_marge_brute)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Taux marge brute</span>
                            <span class="font-semibold {{ $latestKpi->taux_marge_brute < 20 ? 'text-orange-600' : 'text-green-600' }}">
                                {{ \App\Models\Kpi::formatPct((float)$latestKpi->taux_marge_brute) }}
                            </span>
                        </div>
                    @endif
                    @if($latestKpi->ebe)
                        <div class="flex justify-between">
                            <span class="text-gray-500">EBE</span>
                            <span class="font-semibold {{ $latestKpi->ebe < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ \App\Models\Kpi::formatEur((float)$latestKpi->ebe) }}
                            </span>
                        </div>
                    @endif
                    @if($latestKpi->tresorerie_nette)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Trésorerie</span>
                            <span class="font-semibold {{ $latestKpi->tresorerie_nette < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ \App\Models\Kpi::formatEur((float)$latestKpi->tresorerie_nette) }}
                            </span>
                        </div>
                    @endif
                    @if($latestKpi->jours_tresorerie)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Autonomie</span>
                            <span class="text-gray-900">{{ $latestKpi->jours_tresorerie }} jours</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-sm text-gray-500 mb-3">Aucun indicateur calculé</p>
                <a href="{{ route('financial-data.create') }}?client_id={{ $client->id }}"
                   class="text-xs text-indigo-600 hover:underline">Saisir les premières données →</a>
            </div>
        @endif

    </div>

    {{-- ── Colonne principale ────────────────────────────────────────────── --}}
    <div class="col-span-2 space-y-6">

        {{-- Missions --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Missions</h2>
                <a href="{{ route('missions.create') }}?client_id={{ $client->id }}"
                   class="text-xs text-indigo-600 hover:underline">+ Nouvelle mission</a>
            </div>

            @if($client->missions->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400 text-sm">
                    Aucune mission pour ce client.
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($client->missions as $mission)
                        <div class="px-6 py-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full {{ $mission->statutDot() }}"></div>
                                        <a href="{{ route('missions.show', $mission) }}"
                                           class="font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $mission->type_mission }}
                                        </a>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        {{ $mission->date_debut->format('d/m/Y') }} → {{ $mission->date_fin->format('d/m/Y') }}
                                        · {{ $mission->phaseLabel() }}
                                    </div>
                                    {{-- Barre de progression --}}
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="w-32 h-1.5 bg-gray-100 rounded-full">
                                            <div class="h-1.5 bg-indigo-500 rounded-full"
                                                 style="width: {{ $mission->avancementPourcent() }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $mission->avancementPourcent() }}%</span>
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-400">
                                    {{ $mission->action_plans_count ?? 0 }} actions
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Historique données financières --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Données financières</h2>
                <a href="{{ route('financial-data.create') }}?client_id={{ $client->id }}"
                   class="text-xs text-indigo-600 hover:underline">+ Ajouter</a>
            </div>

            @if($client->financialData->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400 text-sm">
                    Aucune donnée financière saisie.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-2.5 text-xs text-gray-500 font-medium">Période</th>
                            <th class="text-right px-4 py-2.5 text-xs text-gray-500 font-medium">CA</th>
                            <th class="text-right px-4 py-2.5 text-xs text-gray-500 font-medium">Marge brute</th>
                            <th class="text-right px-4 py-2.5 text-xs text-gray-500 font-medium">EBE</th>
                            <th class="text-center px-4 py-2.5 text-xs text-gray-500 font-medium">Alerte</th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($client->financialData as $fd)
                            @php $kpi = $fd->kpi ?? null; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $fd->periodeLabel() }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    {{ $fd->ca ? number_format($fd->ca, 0, ',', ' ') . ' €' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($kpi && $kpi->marge_brute !== null)
                                        <span class="{{ $kpi->marge_brute < 0 ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ number_format($kpi->marge_brute, 0, ',', ' ') }} €
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($kpi && $kpi->ebe !== null)
                                        <span class="{{ $kpi->ebe < 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">
                                            {{ number_format($kpi->ebe, 0, ',', ' ') }} €
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($kpi)
                                        <div class="flex justify-center">
                                            <div class="w-2.5 h-2.5 rounded-full {{ $kpi->alerteDot() }}"></div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('financial-data.show', $fd) }}"
                                       class="text-xs text-indigo-600 hover:underline">Détail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</div>

@endsection
