@extends('layouts.app')

@section('title', 'Modifier — ' . $financialData->periodeLabel())
@section('subtitle', $financialData->client->raison_sociale)

@section('header-actions')
    <a href="{{ route('financial-data.show', $financialData) }}" class="text-sm text-gray-500 hover:text-gray-700">← Indicateurs</a>
@endsection

@section('content')

<div class="max-w-4xl">
    <form action="{{ route('financial-data.update', $financialData) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-sm text-indigo-800">
            <strong>Note :</strong> Les indicateurs seront automatiquement recalculés après enregistrement.
        </div>

        {{-- Période (non modifiable) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Période</h2>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-medium text-gray-700">
                    {{ $financialData->periodeLabel() }}
                </span>
                <span class="text-xs text-gray-400">(non modifiable — créez une nouvelle entrée si nécessaire)</span>
            </div>
        </div>

        {{-- Reprend le même formulaire que create mais prérempli --}}
        @include('financial._form', ['model' => $financialData])

        <div class="flex items-center gap-3 pb-6">
            <button type="submit"
                    class="px-6 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                Enregistrer et recalculer les indicateurs →
            </button>
            <a href="{{ route('financial-data.show', $financialData) }}"
               class="px-6 py-3 border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

@endsection
