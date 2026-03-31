@extends('layouts.app')

@section('title', 'Nouveau client')
@section('subtitle', 'Créer une fiche client')

@section('header-actions')
    <a href="{{ route('clients.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Retour</a>
@endsection

@section('content')

<div class="max-w-3xl">
    <form
        x-data="companySearchForm({
            searchUrl: @js(route('api.company.search')),
            enrichUrl: @js(route('api.company.enrich')),
            initial: {
                raison_sociale: @js(old('raison_sociale')),
                adresse: @js(old('adresse')),
                siret: @js(old('siret')),
                forme_juridique: @js(old('forme_juridique')),
                annee_creation: @js(old('annee_creation')),
                secteur: @js(old('secteur')),
            }
        })"
        x-init="init()"
        action="{{ route('clients.store') }}"
        method="POST"
        class="space-y-8"
    >
        @csrf

        {{-- ── Informations générales ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-5">Informations générales</h2>
            <div class="grid grid-cols-2 gap-5">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher une entreprise</label>
                    <div class="relative">
                        <input
                            type="text"
                            x-model="query"
                            @input.debounce.300ms="search()"
                            @keydown.escape="close()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Tapez une raison sociale, un SIREN ou un SIRET…"
                            autocomplete="off"
                        >

                        <div
                            x-show="open"
                            x-transition
                            @click.outside="close()"
                            class="absolute z-20 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
                        >
                            <template x-if="loading">
                                <div class="px-3 py-2 text-sm text-gray-500">Recherche en cours…</div>
                            </template>

                            <template x-if="!loading && error">
                                <div class="px-3 py-2 text-sm text-red-600" x-text="error"></div>
                            </template>

                            <template x-if="!loading && !error && results.length === 0">
                                <div class="px-3 py-2 text-sm text-gray-500">Aucun résultat</div>
                            </template>

                            <template x-for="item in results" :key="item.siret || item.siren || item.label">
                                <button
                                    type="button"
                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 border-t border-gray-100"
                                    @click="select(item)"
                                >
                                    <div class="text-sm font-medium text-gray-900" x-text="item.raison_sociale || item.label"></div>
                                    <div class="text-xs text-gray-500" x-text="(item.siret || item.siren || '') + (item.adresse ? ' — ' + item.adresse : '')"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Données issues de sources publiques (Annuaire des Entreprises). Vérifiez avant enregistrement.
                    </p>
                    <template x-if="enrichWarning">
                        <p class="mt-2 text-xs text-amber-700" x-text="enrichWarning"></p>
                    </template>
                    <template x-if="enrichInfo">
                        <div class="mt-2 text-xs text-gray-600">
                            <span class="font-medium">Sirene</span>
                            <span x-text="' — ' + (enrichInfo.etat_administratif || 'État ?')"></span>
                            <template x-if="enrichInfo.siege !== null">
                                <span x-text="enrichInfo.siege ? ' — Siège' : ' — Établissement'"></span>
                            </template>
                            <template x-if="enrichInfo.code_postal || enrichInfo.commune">
                                <span x-text="' — ' + [enrichInfo.code_postal, enrichInfo.commune].filter(Boolean).join(' ')"></span>
                            </template>
                            <template x-if="enrichInfo.effectif_tranche">
                                <span x-text="' — Effectif: ' + enrichInfo.effectif_tranche + (enrichInfo.effectif_annee ? ' (' + enrichInfo.effectif_annee + ')' : '')"></span>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale *</label>
                    <input type="text" name="raison_sociale" x-model="form.raison_sociale" required
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
                    <input type="text" name="adresse" x-model="form.adresse"
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
                    <input type="text" name="secteur" x-model="form.secteur" required
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
                    <input type="text" name="siret" x-model="form.siret" maxlength="14"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="14 chiffres">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forme juridique</label>
                    <input type="text" name="forme_juridique" x-model="form.forme_juridique"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="SARL, SAS, EI...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Année de création</label>
                    <input type="number" name="annee_creation" x-model="form.annee_creation"
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

<script>
    function companySearchForm({ searchUrl, enrichUrl, initial }) {
        return {
            searchUrl,
            enrichUrl,
            query: '',
            open: false,
            loading: false,
            error: null,
            results: [],
            enrichWarning: null,
            enrichInfo: null,
            form: {
                raison_sociale: initial?.raison_sociale ?? '',
                adresse: initial?.adresse ?? '',
                siret: initial?.siret ?? '',
                forme_juridique: initial?.forme_juridique ?? '',
                annee_creation: initial?.annee_creation ?? '',
                secteur: initial?.secteur ?? '',
            },
            init() {
                // no-op (placeholder if we later want autofocus or prefetch)
            },
            close() {
                this.open = false;
            },
            async search() {
                const q = (this.query || '').trim();
                this.error = null;

                if (q.length < 2) {
                    this.results = [];
                    this.open = false;
                    return;
                }

                this.loading = true;
                this.open = true;

                try {
                    const resp = await window.axios.get(this.searchUrl, { params: { q, per_page: 10 } });
                    this.results = resp?.data?.results ?? [];
                } catch (e) {
                    this.results = [];
                    const msg = e?.response?.data?.message || e?.message || 'Erreur lors de la recherche';
                    this.error = msg;
                } finally {
                    this.loading = false;
                }
            },
            select(item) {
                this.form.raison_sociale = item?.raison_sociale || this.form.raison_sociale;
                this.form.adresse = item?.adresse || this.form.adresse;
                this.form.siret = (item?.siret || this.form.siret || '').toString().replace(/\D+/g, '').slice(0, 14);
                this.form.forme_juridique = item?.forme_juridique || this.form.forme_juridique;
                this.form.annee_creation = item?.annee_creation || this.form.annee_creation;
                this.enrichWarning = null;
                this.enrichInfo = null;

                // Prefill "secteur" with APE/NAF code (libellé complet via table NAF possible en phase 2)
                if (!this.form.secteur && item?.naf) {
                    this.form.secteur = `APE ${item.naf}`;
                }

                this.query = item?.label || this.query;
                this.close();

                if (this.form.siret) {
                    this.enrichFromSirene(this.form.siret);
                }
            },

            async enrichFromSirene(siret) {
                if (!this.enrichUrl) return;

                try {
                    const resp = await window.axios.get(this.enrichUrl, { params: { siret } });
                    const d = resp?.data || {};

                    if (d?.raison_sociale) this.form.raison_sociale = d.raison_sociale;
                    if (d?.adresse) this.form.adresse = d.adresse;
                    if (d?.annee_creation) this.form.annee_creation = d.annee_creation;

                    // Catégorie/forme juridique : l'API Sirene renvoie souvent un code (ex: 5710).
                    // On le met tel quel (ou tu peux ajouter un mapping "code -> libellé" plus tard).
                    if (!this.form.forme_juridique && d?.categorie_juridique) {
                        this.form.forme_juridique = `CJ ${d.categorie_juridique}`;
                    }

                    if (!this.form.secteur && d?.naf) {
                        this.form.secteur = `APE ${d.naf}`;
                    }

                    const ad = d?.adresse_details || {};
                    this.enrichInfo = {
                        etat_administratif: d?.etat_administratif || null,
                        siege: (typeof d?.siege === 'boolean') ? d.siege : null,
                        code_postal: ad?.code_postal || null,
                        commune: ad?.commune || null,
                        effectif_tranche: d?.effectif?.tranche || null,
                        effectif_annee: d?.effectif?.annee || null,
                    };
                } catch (e) {
                    const status = e?.response?.status;
                    const code = e?.response?.data?.error;
                    if (status === 501 && code === 'missing_insee_token') {
                        this.enrichWarning = "Enrichissement Sirene indisponible : ajoutez INSEE_SIRENE_TOKEN dans .env pour remplir forme juridique et année de création automatiquement.";
                    } else {
                        this.enrichWarning = "Enrichissement Sirene indisponible pour le moment (token/quota/réseau).";
                    }
                }
            },
        }
    }
</script>

@endsection
