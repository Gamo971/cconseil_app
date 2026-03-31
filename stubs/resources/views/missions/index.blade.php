@extends('layouts.app')

@section('title', 'Missions')
@section('subtitle', $missions->total() . ' mission(s) au total')

@section('header-actions')
    <a href="{{ route('missions.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle mission
    </a>
@endsection

@section('content')

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    @if($missions->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <p class="text-sm">Aucune mission pour l'instant.</p>
            <a href="{{ route('missions.create') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">
                Créer une première mission →
            </a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mission</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Phase</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Progression</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fin prévue</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($missions as $mission)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <a href="{{ route('clients.show', $mission->client) }}"
                               class="text-gray-600 hover:text-indigo-600 text-xs">
                                {{ $mission->client->raison_sociale }}
                            </a>
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('missions.show', $mission) }}"
                               class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ $mission->type_mission }}
                            </a>
                            @if($mission->actions_en_retard_count > 0)
                                <div class="mt-0.5 text-xs text-red-600">
                                    {{ $mission->actions_en_retard_count }} action(s) en retard
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-600">{{ $mission->phaseLabel() }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-1.5 bg-gray-100 rounded-full">
                                    <div class="h-1.5 bg-indigo-500 rounded-full"
                                         style="width: {{ $mission->avancementPourcent() }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400">{{ $mission->avancementPourcent() }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-600">
                            {{ $mission->date_fin->format('d/m/Y') }}
                            @if($mission->joursRestants() <= 14 && $mission->statut !== 'termine')
                                <div class="text-orange-500 font-medium">J-{{ $mission->joursRestants() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $mission->statutBadge() }}">
                                <div class="w-1.5 h-1.5 rounded-full {{ $mission->statutDot() }}"></div>
                                {{ ucfirst($mission->statut) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('missions.show', $mission) }}"
                               class="text-xs text-indigo-600 hover:underline">Voir →</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($missions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $missions->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
