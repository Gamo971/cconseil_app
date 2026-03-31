<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Mission;
use App\Models\FinancialData;
use App\Policies\ClientPolicy;
use App\Policies\MissionPolicy;
use App\Policies\FinancialDataPolicy;
use App\Services\KpiCalculatorService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistrement du service KPI comme singleton
        $this->app->singleton(KpiCalculatorService::class);
    }

    public function boot(): void
    {
        // Enregistrement des Policies
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Mission::class, MissionPolicy::class);
        Gate::policy(FinancialData::class, FinancialDataPolicy::class);
    }
}
