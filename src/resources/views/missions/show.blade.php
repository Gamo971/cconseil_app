@extends('layouts.app')

@section('title', $mission->client->raison_sociale . ' — ' . $mission->type_mission)
@section('subtitle', $mission->phaseLabel() . ' · Fin le ' . $mission->date_fin->format('d/m/Y'))

@section('header-actions')
    <a href="{{ route('clients.show', $mission->client) }}" class="text-sm text-gray-500 hover:text-gray-700">← Client</a>
    <a href="{{ route('missions.edit', $mission) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
        Modifier la mission
    </a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-6">

    {{-- ── Colonne gauche : statut mission ──────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Statut & progression --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Avancement</h2>

            {{-- Statut badge --}}
            <div class="flex items-center gap-2 mb-4">
                <div class="w-3 h-3 rounded-full {{ $mission->statutDot() }}"></div>
                <span class="text-sm font-medium {{ match($mission->statut) {
                    'rouge' => 'text-red-600', 'orange' => 'text-orange-600', default => 'text-gray-700'
                } }}">{{ ucfirst($mission->statut) }}</span>
            </div>

            {{-- Phases --}}
            <div class="space-y-2">
                @foreach([
                    'phase_1_diagnostic'   => 'Phase 1 — Diagnostic',
                    'phase_2_plan_action'  => 'Phase 2 — Plan d\'action',
                    'phase_3_pilotage'     => 'Phase 3 — Pilotage',
                    'phase_4_optimisation' => 'Phase 4 — Optimisation',
                ] as $phase => $label)
                    @php
                        $phases = ['phase_1_diagnostic','phase_2_plan_action','phase_3_pilotage','phase_4_optimisation','terminee'];
                        $currentIdx = array_search($mission->phase_courante, $phases);
                        $phaseIdx = array_search($phase, $phases);
                        $isDone = $currentIdx > $phaseIdx;
                        $isCurrent = $mission->phase_courante === $phase;
                    @endphp
                    <div class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $isDone ? 'bg-green-500' : ($isCurrent ? 'bg-indigo-600' : 'bg-gray-200') }}">
                            @if($isDone)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @elseif($isCurrent)
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            @endif
                        </div>
                        <span class="text-xs {{ $isCurrent ? 'font-semibold text-indigo-700' : ($isDone ? 'text-gray-500 line-through' : 'text-gray-400') }}">
                            {{ $label }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Barre globale --}}
            <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Progression globale</span>
                    <span>{{ $mission->avancementPourcent() }}%</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full">
                    <div class="h-2 bg-indigo-500 rounded-full transition-all"
                         style="width: {{ $mission->avancementPourcent() }}%"></div>
                </div>
            </div>

            {{-- Jours restants --}}
            <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                <div class="text-2xl font-bold {{ $mission->joursRestants() <= 14 ? 'text-orange-600' : 'text-gray-900' }}">
                    J-{{ $mission->joursRestants() }}
                </div>
                <div class="text-xs text-gray-400">jours restants</div>
            </div>
        </div>

        {{-- Infos mission --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Détails</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Début</span>
                    <span class="text-gray-900">{{ $mission->date_debut->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Fin prévue</span>
                    <span class="text-gray-900">{{ $mission->date_fin->format('d/m/Y') }}</span>
                </div>
                @if($mission->honoraires_ht)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Honoraires HT</span>
                        <span class="text-gray-900 font-medium">{{ number_format($mission->honoraires_ht, 0, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Facturation</span>
                        <span class="text-gray-900 capitalize">{{ $mission->mode_facturation }}</span>
                    </div>
                @endif
            </div>

            @if($mission->objectif_global)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500 font-medium mb-1">Objectif global</p>
                    <p class="text-xs text-gray-700 leading-relaxed">{{ $mission->objectif_global }}</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ── Colonne principale : Plan d'action ───────────────────────────── --}}
    <div class="col-span-2 space-y-6">

        {{-- Plan d'action --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Plan d'action</h2>
                <span class="text-xs text-gray-400">Max. 3 objectifs prioritaires</span>
            </div>

            {{-- Formulaire ajout action --}}
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter une action
                </button>

                <div x-show="open" x-cloak class="mt-4">
                    <form action="{{ route('missions.actions.store', $mission) }}" method="POST"
                          class="grid grid-cols-2 gap-3">
                        @csrf

                        <div class="col-span-2">
                            <input type="text" name="objectif" placeholder="Objectif (ex: +15% panier moyen)" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <input type="text" name="kpi_cible" placeholder="KPI cible (ex: +15%)" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <input type="number" name="impact_estime_eur" placeholder="Impact estimé (€)" step="100"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <input type="text" name="responsable" placeholder="Responsable"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <input type="date" name="date_limite" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <select name="priorite" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="1">🔴 Priorité haute</option>
                                <option value="2" selected>🟠 Priorité normale</option>
                                <option value="3">🟢 Priorité basse</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                                Ajouter
                            </button>
                            <button type="button" @click="open = false"
                                    class="px-4 py-2 border border-gray-300 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                                Annuler
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            {{-- Liste des actions --}}
            @if($mission->actionPlans->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400 text-sm">
                    Aucune action définie. Commencez par ajouter vos 3 objectifs prioritaires.
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($mission->actionPlans as $action)
                        <div class="px-6 py-4">
                            <div class="flex items-start gap-4">

                                {{-- Statut dropdown --}}
                                <form action="{{ route('missions.actions.update-statut', [$mission, $action]) }}"
                                      method="POST" class="flex-shrink-0 mt-0.5">
                                    @csrf @method('PATCH')
                                    <select name="statut" onchange="this.form.submit()"
                                            class="text-xs border border-gray-200 rounded px-1.5 py-1 {{ $action->alerteBadge() }}">
                                        <option value="non_commence" {{ $action->statut === 'non_commence' ? 'selected' : '' }}>Non commencé</option>
                                        <option value="en_cours" {{ $action->statut === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                        <option value="termine" {{ $action->statut === 'termine' ? 'selected' : '' }}>Terminé ✓</option>
                                        <option value="en_retard" {{ $action->statut === 'en_retard' ? 'selected' : '' }}>En retard</option>
                                    </select>
                                </form>

                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        @if($action->priorite == 1)
                                            <span class="text-xs font-medium text-red-600">HAUTE</span>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900 {{ $action->statut === 'termine' ? 'line-through text-gray-400' : '' }}">
                                            {{ $action->objectif }}
                                        </span>
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-400">
                                        <span>KPI : <strong class="text-gray-700">{{ $action->kpi_cible }}</strong></span>
                                        @if($action->impact_estime_eur)
                                            <span>Impact : <strong class="text-green-700">+{{ number_format($action->impact_estime_eur, 0, ',', ' ') }} €</strong></span>
                                        @endif
                                        @if($action->responsable)
                                            <span>👤 {{ $action->responsable }}</span>
                                        @endif
                                        <span class="{{ $action->alerte === 'rouge' ? 'text-red-600 font-medium' : '' }}">
                                            📅 {{ $action->date_limite->format('d/m/Y') }}
                                        </span>
                                    </div>

                                    {{-- Barre de progression si valeurs renseignées --}}
                                    @if($action->valeur_cible && $action->valeur_actuelle)
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full">
                                                <div class="h-1.5 bg-indigo-500 rounded-full"
                                                     style="width: {{ $action->avancementPourcent() }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $action->avancementPourcent() }}%</span>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Comptes rendus de réunion --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Comptes rendus de réunion</h2>
                <span class="text-xs text-gray-400 bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded">Sprint 2 — IA</span>
            </div>

            @if($mission->meetingReports->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400 text-sm">
                    La génération automatique de comptes rendus sera disponible en Sprint 2.
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($mission->meetingReports as $report)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $report->titre }}</div>
                                    <div class="text-xs text-gray-400">{{ $report->date_reunion->format('d/m/Y') }}</div>
                                </div>
                                @if($report->genere_par_ia)
                                    <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded">IA</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
