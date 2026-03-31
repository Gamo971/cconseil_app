@extends('layouts.app')

@section('title', 'Analyse IA — ' . $client->raison_sociale)

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analyse IA</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $client->raison_sociale }} · {{ $client->secteur ?? 'Secteur non renseigné' }}</p>
        </div>
        <a href="{{ route('clients.show', $client) }}"
           class="text-sm text-indigo-600 hover:text-indigo-800">
            ← Retour au client
        </a>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show"
             class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex justify-between">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-800">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Cas : pas de données financières --}}
    @if(!$latestKpi || !$latestFd)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="text-4xl mb-3">📊</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune donnée financière</h3>
            <p class="text-gray-500 text-sm mb-4">Saisissez d'abord des données financières pour ce client afin de pouvoir générer une analyse.</p>
            <a href="{{ route('financial.create', $client) }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Saisir les données financières
            </a>
        </div>
    @else
        {{-- Bloc "Lancer une analyse" --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 mb-1">
                        @if($kpi && $kpi->analyse_ia)
                            Dernière analyse — {{ $kpi->periodeLabel() }}
                        @else
                            Aucune analyse disponible
                        @endif
                    </h2>
                    <p class="text-sm text-gray-500">
                        Données analysées : {{ $latestKpi->periodeLabel() }}
                        · Alerte :
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $latestKpi->alerteBadge() }}">
                            {{ strtoupper($latestKpi->alerte ?? 'NC') }}
                        </span>
                    </p>
                </div>
                <form method="POST" action="{{ route('clients.analysis.generate', $client) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ $kpi && $kpi->analyse_ia ? 'Relancer l\'analyse' : 'Générer l\'analyse' }}
                    </button>
                </form>
            </div>
        </div>

        @if($kpi && $kpi->analyse_ia)
            @php $analyse = json_decode($kpi->analyse_ia, true); @endphp

            {{-- Diagnostic --}}
            @if(!empty($analyse['diagnostic']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2"></span>
                    Diagnostic financier
                </h3>
                <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $analyse['diagnostic'] }}</p>
            </div>
            @endif

            {{-- Recommandations --}}
            @if(!empty($analyse['recommandations']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Recommandations prioritaires (90 jours)
                </h3>
                <div class="space-y-2">
                    @foreach(explode("\n", trim($analyse['recommandations'])) as $action)
                        @if(trim($action))
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">
                                {{ $loop->iteration }}
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ preg_replace('/^\d+\.\s*/', '', trim($action)) }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Synthèse --}}
            @if(!empty($analyse['synthese']))
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6 mb-6">
                <h3 class="text-sm font-semibold text-indigo-800 uppercase tracking-wide mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Synthèse exécutive
                </h3>
                <p class="text-indigo-900 text-sm leading-relaxed">{{ $analyse['synthese'] }}</p>
            </div>
            @endif

            {{-- Actions export PDF --}}
            <div class="flex items-center space-x-3">
                <a href="{{ route('clients.pdf.download', $client) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Télécharger le rapport PDF
                </a>
                <a href="{{ route('clients.show', $client) }}"
                   class="text-sm text-gray-500 hover:text-gray-700">
                    Retour à la fiche client
                </a>
            </div>

        @endif {{-- fin if analyse_ia --}}
    @endif {{-- fin if latestKpi --}}

</div>
@endsection
