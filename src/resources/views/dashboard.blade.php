@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('subtitle', 'Vue d\'ensemble de votre portefeuille')

@section('header-actions')
    <a href="{{ route('clients.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        Nouveau client
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500">Clients actifs</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['clients_actifs'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500">Clients (total)</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['clients_total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500">Missions en cours</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['missions_en_cours'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500">Actions en retard</p>
                <p class="mt-1 text-2xl font-semibold text-amber-700">{{ $stats['actions_en_retard'] }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Alertes rouge (KPI)</h2>
                @if($clientsEnAlerte->isEmpty())
                    <p class="text-sm text-gray-400">Aucun client en alerte rouge.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($clientsEnAlerte as $client)
                            <li class="py-3">
                                <a href="{{ route('clients.show', $client) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                    {{ $client->raison_sociale }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Répartition alertes</h2>
                <dl class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <dt class="text-xs text-gray-500">Vert</dt>
                        <dd class="text-lg font-semibold text-emerald-600">{{ $repartitionAlertes['vert'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Orange</dt>
                        <dd class="text-lg font-semibold text-amber-600">{{ $repartitionAlertes['orange'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Rouge</dt>
                        <dd class="text-lg font-semibold text-red-600">{{ $repartitionAlertes['rouge'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Missions actives</h2>
                <a href="{{ route('missions.index') }}" class="text-xs text-indigo-600 hover:underline">Voir tout</a>
            </div>
            @if($missionsActives->isEmpty())
                <p class="text-sm text-gray-400">Aucune mission en cours.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($missionsActives as $mission)
                        <li class="py-3 flex justify-between gap-4">
                            <div>
                                <a href="{{ route('missions.show', $mission) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $mission->client->raison_sociale ?? '—' }} — {{ $mission->type_mission }}
                                </a>
                                <p class="text-xs text-gray-500">Fin {{ $mission->date_fin?->format('d/m/Y') }}</p>
                            </div>
                            <span class="text-xs text-gray-500 shrink-0">{{ $mission->statut }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Actions urgentes (7 jours)</h2>
            @if($actionsUrgentes->isEmpty())
                <p class="text-sm text-gray-400">Aucune action urgente.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($actionsUrgentes as $action)
                        <li class="py-3">
                            <a href="{{ route('missions.show', $action->mission) }}" class="text-sm text-indigo-600 hover:underline">
                                {{ $action->objectif ?? 'Action' }}
                            </a>
                            <span class="text-xs text-gray-500"> · échéance {{ $action->date_limite?->format('d/m/Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
