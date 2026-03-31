@extends('layouts.app')

@section('title', 'Modifier — ' . $client->raison_sociale)

@section('header-actions')
    <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-500 hover:text-gray-700">← Retour</a>
@endsection

@section('content')

<div class="max-w-3xl">
    <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Informations générales</h2>
            <div class="grid grid-cols-2 gap-5">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale *</label>
                    <input type="text" name="raison_sociale" value="{{ old('raison_sociale', $client->raison_sociale) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact principal</label>
                    <input type="text" name="nom_contact" value="{{ old('nom_contact', $client->nom_contact) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $client->telephone) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                    <select name="statut" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(['prospect' => 'Prospect', 'actif' => 'Actif', 'en_pause' => 'En pause', 'termine' => 'Terminé'] as $val => $label)
                            <option value="{{ $val }}" {{ old('statut', $client->statut) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'activité *</label>
                    <select name="type_activite" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(['service' => 'Service', 'negoce' => 'Négoce', 'production' => 'Production', 'mixte' => 'Mixte'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type_activite', $client->type_activite) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secteur *</label>
                    <input type="text" name="secteur" value="{{ old('secteur', $client->secteur) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SIRET</label>
                    <input type="text" name="siret" value="{{ old('siret', $client->siret) }}" maxlength="14"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forme juridique</label>
                    <input type="text" name="forme_juridique" value="{{ old('forme_juridique', $client->forme_juridique) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année de création</label>
                    <input type="number" name="annee_creation" value="{{ old('annee_creation', $client->annee_creation) }}"
                           min="1900" max="{{ date('Y') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $client->notes) }}</textarea>
                </div>

            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                Enregistrer les modifications
            </button>
            <a href="{{ route('clients.show', $client) }}"
               class="px-6 py-2.5 border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
