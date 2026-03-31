{{-- Partial réutilisé dans create et edit --}}
@php $m = $model ?? null; @endphp

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-5">Chiffre d'affaires & Achats</h2>
    <div class="grid grid-cols-3 gap-5">
        @foreach(['ca' => 'CA HT', 'achats_marchandises' => 'Achats marchandises', 'autres_achats' => 'Autres achats'] as $field => $label)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                <div class="relative">
                    <input type="number" name="{{ $field }}" value="{{ old($field, $m?->$field) }}" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
                    <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-5">Charges</h2>
    <div class="grid grid-cols-3 gap-5">
        @foreach(['charges_fixes' => 'Charges fixes', 'charges_variables' => 'Charges variables', 'amortissements' => 'Amortissements'] as $field => $label)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                <div class="relative">
                    <input type="number" name="{{ $field }}" value="{{ old($field, $m?->$field) }}" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
                    <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-5">Masse salariale</h2>
    <div class="grid grid-cols-3 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Salaires bruts</label>
            <div class="relative">
                <input type="number" name="masse_salariale_brute" value="{{ old('masse_salariale_brute', $m?->masse_salariale_brute) }}" min="0" step="0.01"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
                <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Charges patronales</label>
            <div class="relative">
                <input type="number" name="charges_patronales" value="{{ old('charges_patronales', $m?->charges_patronales) }}" min="0" step="0.01"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
                <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de salariés</label>
            <input type="number" name="nombre_salaries" value="{{ old('nombre_salaries', $m?->nombre_salaries) }}" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-5">Dettes & Trésorerie</h2>
    <div class="grid grid-cols-3 gap-5">
        @foreach([
            'dette_totale' => 'Dette totale',
            'dette_fournisseurs' => 'Dette fournisseurs',
            'dette_fiscale_sociale' => 'Dette fiscale & sociale',
            'tresorerie_debut' => 'Trésorerie début',
            'tresorerie_fin' => 'Trésorerie fin',
            'investissements' => 'Investissements',
        ] as $field => $label)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                <div class="relative">
                    <input type="number" name="{{ $field }}" value="{{ old($field, $m?->$field) }}" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="0">
                    <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-3">Notes</h2>
    <textarea name="notes" rows="3"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
              placeholder="Contexte particulier, données estimées...">{{ old('notes', $m?->notes) }}</textarea>
</div>
