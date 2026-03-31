@extends('layouts.app')

@section('title', 'Clients')
@section('subtitle', $clients->total() . ' client(s) dans votre portefeuille')

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

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    @if($clients->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium">Aucun client pour l'instant</p>
            <a href="{{ route('clients.create') }}"
               class="mt-3 inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                Créer votre premier client
            </a>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Secteur</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Missions</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Alerte KPI</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($clients as $client)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div>
                                <a href="{{ route('clients.show', $client) }}"
                                   class="font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $client->raison_sociale }}
                                </a>
                                @if($client->nom_contact)
                                    <div class="text-xs text-gray-400">{{ $client->nom_contact }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-gray-700">{{ $client->secteur }}</div>
                            <div class="text-xs text-gray-400">{{ $client->typeActiviteLabel() }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $client->statutBadge() }}">
                                {{ $client->statutLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @if($client->missions_count > 0)
                                <span class="text-gray-700">{{ $client->missions_count }} en cours</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($kpi = $client->kpis->first())
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full {{ $kpi->alerteDot() }}"></div>
                                    <span class="text-xs text-gray-600 capitalize">{{ $kpi->alerte }}</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Non renseigné</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('clients.show', $client) }}"
                                   class="text-xs text-indigo-600 hover:underline">Voir</a>
                                <a href="{{ route('financial-data.create') }}?client_id={{ $client->id }}"
                                   class="text-xs text-gray-500 hover:text-gray-700 hover:underline">+ Données</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($clients->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $clients->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
