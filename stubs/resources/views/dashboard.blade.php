@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('subtitle', 'Vue globale de votre portefeuille')

@section('header-actions')
    <a href="{{ route('clients.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau client
    </a>
@endsection

@section('content')

{{-- ── KPIs globaux ──────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-5 mb-8">

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clients actifs</span>
            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">{{ $stats['clients_actifs'] }}</div>
        <div class="text-xs text-gray-400 mt-1">sur {{ $stats['clients_total'] }} au total</div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Missions en cours</span>
            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">{{ $stats['missions_en_cours'] }}</div>
        <div class="text-xs text-gray-400 mt-1">missions actives</div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Actions en retard</span>
            <div class="w-8 h-8 {{ $stats['actions_en_retard'] > 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 {{ $stats['actions_en_retard'] > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold {{ $stats['actions_en_retard'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
            {{ $stats['actions_en_retard'] }}
        </div>
        <div class="text-xs text-gray-400 mt-1">actions dépassées</div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clients en alerte</span>
            <div class="w-8 h-8 {{ $repartitionAlertes['rouge'] > 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 {{ $repartitionAlertes['rouge'] > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-1">
            @if($repartitionAlertes['rouge'] > 0)
                <span class="text-xl font-bold text-red-600">{{ $repartitionAlertes['rouge'] }} 🔴</span>
            @endif
            @if($repartitionAlertes['orange'] > 0)
                <span class="text-xl font-bold text-orange-500">{{ $repartitionAlertes['orange'] }} 🟠</span>
            @endif
            @if($repartitionAlertes['vert'] > 0)
                <span class="text-xl font-bold text-green-600">{{ $repartitionAlertes['vert'] }} 🟢</span>
            @endif
            @if($repartitionAlertes['rouge'] == 0 && $repartitionAlertes['orange'] == 0)
                <span class="text-3xl font-bold text-gray-900">—</span>
            @endif
        </div>
        <div class="text-xs text-gray-400 mt-1">niveaux d'alerte</div>
    </div>

</div>

<div class="grid grid-cols-3 gap-6">

    {{-- ── Missions actives ──────────────────────────────────────────────── --}}
    <div class="col-span-2 bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Missions en cours</h2>
            <a href="{{ route('missions.index') }}" class="text-xs text-indigo-600 hover:underline">Voir tout</a>
        </div>

        @if($missionsActives->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm">Aucune mission en cours</p>
                <a href="{{ route('missions.create') }}" class="mt-2 inline-block text-xs text-indigo-600 hover:underline">Créer une mission →</a>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($missionsActives as $mission)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full {{ $mission->statutDot() }}"></div>
                                    <a href="{{ route('missions.show', $mission) }}"
                                       class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $mission->client->raison_sociale }}
                                    </a>
                                    <span class="text-xs text-gray-400">— {{ $mission->type_mission }}</span>
                                </div>

                                {{-- Barre de progression phase --}}
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full">
                                        <div class="h-1.5 bg-indigo-500 rounded-full transition-all"
                                             style="width: {{ $mission->avancementPourcent() }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ $mission->phaseLabel() }}</span>
                                </div>

                                <div class="mt-1 text-xs text-gray-400">
                                    Fin prévue : {{ $mission->date_fin->format('d/m/Y') }}
                                    @if($mission->joursRestants() <= 14 && $mission->joursRestants() > 0)
                                        <span class="text-orange-500 font-medium ml-1">(J-{{ $mission->joursRestants() }})</span>
                                    @elseif($mission->joursRestants() == 0)
                                        <span class="text-red-500 font-medium ml-1">(Échéance aujourd'hui)</span>
                                    @endif
                                </div>
                            </div>

                            @if($mission->action_plans_count > 0)
                                <span class="ml-4 text-xs text-gray-400">
                                    {{ $mission->action_plans_count }} actions
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Colonne droite ───────────────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Clients en alerte rouge --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                <h2 class="font-semibold text-gray-900 text-sm">Alertes critiques</h2>
            </div>

            @if($clientsEnAlerte->isEmpty())
                <div class="px-5 py-6 text-center text-gray-400 text-sm">
                    <span class="text-2xl">🟢</span>
                    <p class="mt-2 text-xs">Aucune alerte critique</p>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($clientsEnAlerte as $client)
                        <div class="px-5 py-3 hover:bg-gray-50 transition">
                            <a href="{{ route('clients.show', $client) }}"
                               class="block text-sm font-medium text-gray-900 hover:text-indigo-600">
                                {{ $client->raison_sociale }}
                            </a>
                            <div class="text-xs text-gray-400">{{ $client->secteur }}</div>
                            @if($client->kpis->first())
                                <div class="mt-1 text-xs text-red-600 font-medium">
                                    Indicateurs en alerte — {{ $client->kpis->first()->periodeLabel() }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Actions urgentes --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center gap-2 px-5 py-4 border-b border-gray-100">
                <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                <h2 class="font-semibold text-gray-900 text-sm">Actions urgentes (7j)</h2>
            </div>

            @if($actionsUrgentes->isEmpty())
                <div class="px-5 py-6 text-center text-gray-400 text-xs">
                    Aucune action urgente
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($actionsUrgentes as $action)
                        <div class="px-5 py-3">
                            <div class="text-xs font-medium text-gray-800">{{ Str::limit($action->objectif, 45) }}</div>
                            <div class="text-xs text-gray-400">{{ $action->mission->client->raison_sociale }}</div>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $action->alerteBadge() }}">
                                    {{ $action->date_limite->format('d/m') }}
                                </span>
                                @if($action->responsable)
                                    <span class="text-xs text-gray-400">{{ $action->responsable }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
