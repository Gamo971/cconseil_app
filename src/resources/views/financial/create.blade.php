@extends('layouts.app')

@section('title', 'Saisie des données financières')
@section('subtitle', $client->raison_sociale)

@section('header-actions')
    <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-500 hover:text-gray-700">← Client</a>
@endsection

@section('content')

<div class="max-w-4xl">
    <form action="{{ route('financial-data.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">

        {{-- Bandeau info --}}
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-indigo-800">
                <strong>Calcul automatique :</strong> les indicateurs (seuil de rentabilité, EBE, marge brute, trésorerie)
                seront calculés et enregistrés automatiquement dès la validation du formulaire.
            </div>
        </div>

        {{-- ── Période ──────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Période concernée</h2>
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année *</label>
                    <select name="annee" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($anneesCourantes as $annee)
                            <option value="{{ $annee }}" {{ old('annee', date('Y')) == $annee ? 'selected' : '' }}>
                                {{ $annee }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mois <span class="text-gray-400">(vide = annuel)</span></label>
                    <select name="mois"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Données annuelles</option>
                        @foreach([1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
                                  7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'] as $n => $nom)
                            <option value="{{ $n }}" {{ old('mois') == $n ? 'selected' : '' }}>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Chiffre d'affaires & Achats ─────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Chiffre d'affaires & Achats</h2>
            <p class="text-xs text-gray-400 mb-5">Saisir les montants en euros HT</p>
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CA HT</label>
                    <div class="relative">
                        <input type="number" name="ca" value="{{ old('ca') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Achats marchandises</label>
                    <div class="relative">
                        <input type="number" name="achats_marchandises" value="{{ old('achats_marchandises') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Autres achats</label>
                    <div class="relative">
                        <input type="number" name="autres_achats" value="{{ old('autres_achats') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Charges ───────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Charges</h2>
            <p class="text-xs text-gray-400 mb-5">Loyer, assurances, énergie, fournitures...</p>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Charges fixes</label>
                    <p class="text-xs text-gray-400 mb-1">Loyer, assurances, abonnements</p>
                    <div class="relative">
                        <input type="number" name="charges_fixes" value="{{ old('charges_fixes') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Charges variables</label>
                    <p class="text-xs text-gray-400 mb-1">Matières, emballages, commissions</p>
                    <div class="relative">
                        <input type="number" name="charges_variables" value="{{ old('charges_variables') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amortissements</label>
                    <div class="relative">
                        <input type="number" name="amortissements" value="{{ old('amortissements') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Masse salariale ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Masse salariale</h2>
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salaires bruts</label>
                    <div class="relative">
                        <input type="number" name="masse_salariale_brute" value="{{ old('masse_salariale_brute') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Charges patronales</label>
                    <div class="relative">
                        <input type="number" name="charges_patronales" value="{{ old('charges_patronales') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de salariés</label>
                    <input type="number" name="nombre_salaries" value="{{ old('nombre_salaries') }}" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                           placeholder="0">
                </div>
            </div>
        </div>

        {{-- ── Dettes & Trésorerie ───────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Dettes & Trésorerie</h2>
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dette totale</label>
                    <div class="relative">
                        <input type="number" name="dette_totale" value="{{ old('dette_totale') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dette fournisseurs</label>
                    <div class="relative">
                        <input type="number" name="dette_fournisseurs" value="{{ old('dette_fournisseurs') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dette fiscale & sociale</label>
                    <div class="relative">
                        <input type="number" name="dette_fiscale_sociale" value="{{ old('dette_fiscale_sociale') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trésorerie début</label>
                    <div class="relative">
                        <input type="number" name="tresorerie_debut" value="{{ old('tresorerie_debut') }}" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trésorerie fin <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-400 mb-1">Solde bancaire fin de période</p>
                    <div class="relative">
                        <input type="number" name="tresorerie_fin" value="{{ old('tresorerie_fin') }}" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Investissements</label>
                    <div class="relative">
                        <input type="number" name="investissements" value="{{ old('investissements') }}" min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500"
                               placeholder="0">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Notes</h2>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                      placeholder="Contexte particulier, données estimées, sources...">{{ old('notes') }}</textarea>
        </div>

        {{-- Boutons --}}
        <div class="flex items-center gap-3 pb-6">
            <button type="submit"
                    class="px-6 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                Enregistrer et calculer les indicateurs →
            </button>
            <a href="{{ route('clients.show', $client) }}"
               class="px-6 py-3 border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
