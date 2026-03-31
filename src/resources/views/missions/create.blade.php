@extends('layouts.app')

@section('title', 'Nouvelle mission')

@section('header-actions')
    <a href="{{ route('missions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Retour</a>
@endsection

@section('content')

<div class="max-w-2xl">
    <form action="{{ route('missions.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Informations de mission</h2>
            <div class="space-y-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                    <select name="client_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Sélectionner un client —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ (old('client_id', $clientSelectionne?->id) == $client->id) ? 'selected' : '' }}>
                                {{ $client->raison_sociale }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de mission *</label>
                    <input type="text" name="type_mission" value="{{ old('type_mission', 'Restructuration 90 jours') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ex : Restructuration 90 jours, Diagnostic flash, Accompagnement pilotage...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Objectif global</label>
                    <textarea name="objectif_global" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Stabiliser la trésorerie, atteindre le seuil de rentabilité...">{{ old('objectif_global') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                        <input type="date" name="date_debut" value="{{ old('date_debut', date('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
                        <input type="date" name="date_fin" value="{{ old('date_fin', date('Y-m-d', strtotime('+90 days'))) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phase de démarrage</label>
                        <select name="phase_courante" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="phase_1_diagnostic" selected>Phase 1 — Diagnostic</option>
                            <option value="phase_2_plan_action">Phase 2 — Plan d'action</option>
                            <option value="phase_3_pilotage">Phase 3 — Pilotage</option>
                            <option value="phase_4_optimisation">Phase 4 — Optimisation</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut initial</label>
                        <select name="statut" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="vert" selected>🟢 Vert</option>
                            <option value="orange">🟠 Orange</option>
                            <option value="rouge">🔴 Rouge</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Honoraires</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant HT</label>
                    <input type="number" name="honoraires_ht" value="{{ old('honoraires_ht') }}" min="0" step="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode de facturation</label>
                    <select name="mode_facturation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="forfait" selected>Forfait</option>
                        <option value="mensuel">Mensuel</option>
                        <option value="journalier">Journalier</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                Créer la mission
            </button>
            <a href="{{ route('missions.index') }}"
               class="px-6 py-2.5 border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
