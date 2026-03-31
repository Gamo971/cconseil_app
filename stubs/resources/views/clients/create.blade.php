@extends('layouts.app')

@section('title', 'Nouveau client')
@section('subtitle', 'Créer une fiche client')

@section('header-actions')
    <a href="{{ route('clients.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Retour</a>
@endsection

@section('content')

<div class="max-w-3xl">
    <form action="{{ route('clients.store') }}" method="POST" class="space-y-8">
        @csrf

        {{-- ── Informations générales ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Informations générales</h2>
            <div class="grid grid-cols-2 gap-5">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale *</label>
                    <input type="text" name="raison_sociale" value="{{ old('raison_sociale') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('raison_sociale') border-red-400 @enderror"
                           placeholder="Ex : Kamela Beauty Spa">
                    @error('raison_sociale')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact principal</label>
                    <input type="text" name="nom_contact" value="{{ old('nom_contact') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Prénom Nom">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="06 XX XX XX XX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                    <select name="statut" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="prospect" {{ old('statut') === 'prospect' ? 'selected' : '' }}>Prospect</option>
                        <option value="actif" {{ old('statut', 'actif') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="en_pause" {{ old('statut') === 'en_pause' ? 'selected' : '' }}>En pause</option>
                        <option value="termine" {{ old('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

            </div>
        </div>

        {{-- ── Activité et secteur ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Activité</h2>
            <div class="grid grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'activité *</label>
                    <select name="type_activite" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="service" {{ old('type_activite', 'service') === 'service' ? 'selected' : '' }}>Service</option>
                        <option value="negoce" {{ old('type_activite') === 'negoce' ? 'selected' : '' }}>Négoce</option>
                        <option value="production" {{ old('type_activite') === 'production' ? 'selected' : '' }}>Production</option>
                        <option value="mixte" {{ old('type_activite') === 'mixte' ? 'selected' : '' }}>Mixte</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secteur *</label>
                    <input type="text" name="secteur" value="{{ old('secteur') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('secteur') border-red-400 @enderror"
                           placeholder="Ex : Bien-être & Beauté, Restauration, BTP...">
                    @error('secteur')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ── Données juridiques ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Données juridiques <span class="text-sm font-normal text-gray-400">(optionnel)</span></h2>
            <div class="grid grid-cols-3 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SIRET</label>
                    <input type="text" name="siret" value="{{ old('siret') }}" maxlength="14"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="14 chiffres">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forme juridique</label>
                    <input type="text" name="forme_juridique" value="{{ old('forme_juridique') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="SARL, SAS, EI...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année de création</label>
                    <input type="number" name="annee_creation" value="{{ old('annee_creation') }}"
                           min="1900" max="{{ date('Y') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

            </div>
        </div>

        {{-- ── Notes ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Notes internes</h2>
            <textarea name="notes" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Contexte, historique, informations utiles...">{{ old('notes') }}</textarea>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                Créer le client
            </button>
            <a href="{{ route('clients.index') }}"
               class="px-6 py-2.5 border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
