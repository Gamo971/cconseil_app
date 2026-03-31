@extends('layouts.app')

@section('title', 'Import CSV — ' . $client->raison_sociale)

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import CSV</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $client->raison_sociale }}</p>
        </div>
        <a href="{{ route('clients.show', $client) }}"
           class="text-sm text-indigo-600 hover:text-indigo-800">
            ← Retour au client
        </a>
    </div>

    {{-- Formulaire upload --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Importer un fichier CSV</h2>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('clients.csv.import', $client) }}"
              enctype="multipart/form-data">
            @csrf

            <div x-data="{ dragging: false, fileName: '' }"
                 class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fichier CSV <span class="text-red-500">*</span>
                </label>
                <div
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="
                        dragging = false;
                        fileName = $event.dataTransfer.files[0]?.name ?? '';
                        $refs.fileInput.files = $event.dataTransfer.files;
                    "
                    :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 bg-gray-50'"
                    class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors"
                    @click="$refs.fileInput.click()">

                    <input type="file"
                           name="csv_file"
                           accept=".csv,.txt"
                           x-ref="fileInput"
                           class="hidden"
                           @change="fileName = $event.target.files[0]?.name ?? ''">

                    <div x-show="!fileName">
                        <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500">Glissez un fichier CSV ici ou <span class="text-indigo-600 font-medium">cliquez pour choisir</span></p>
                        <p class="text-xs text-gray-400 mt-1">Format .csv ou .txt · max 2 Mo</p>
                    </div>
                    <div x-show="fileName" class="text-sm text-indigo-700 font-medium">
                        📄 <span x-text="fileName"></span>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Importer et calculer les KPIs
            </button>
        </form>
    </div>

    {{-- Modèle CSV --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Format attendu</h3>
        <div class="bg-gray-50 rounded-lg p-3 overflow-x-auto mb-3">
            <code class="text-xs text-gray-700 font-mono whitespace-nowrap">
                annee,mois,ca,achats_marchandises,autres_achats,charges_variables,charges_fixes,masse_salariale_brute,charges_patronales,amortissements,tresorerie_fin,nombre_salaries
            </code>
        </div>
        <p class="text-xs text-gray-500 mb-3">
            Toutes les valeurs financières sont en euros (sans symbole). La colonne <code class="bg-gray-100 px-1 rounded">mois</code> est optionnelle (laisser vide pour des données annuelles).
        </p>
        <a href="{{ route('csv.template') }}"
           class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Télécharger le modèle CSV
        </a>
    </div>

    {{-- Règles de traitement --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
        <p class="font-medium mb-1">ℹ️ Traitement automatique</p>
        <ul class="text-xs space-y-1 text-blue-700">
            <li>• Si une ligne existe déjà (même client + année + mois), elle sera mise à jour.</li>
            <li>• Les KPIs sont recalculés automatiquement pour chaque ligne importée.</li>
            <li>• Les erreurs de format sont signalées sans bloquer les autres lignes.</li>
        </ul>
    </div>

</div>
@endsection
