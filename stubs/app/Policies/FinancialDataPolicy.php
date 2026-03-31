<?php

namespace App\Policies;

use App\Models\FinancialData;
use App\Models\User;

class FinancialDataPolicy
{
    public function view(User $user, FinancialData $financialData): bool
    {
        return $user->id === $financialData->client->user_id;
    }

    public function update(User $user, FinancialData $financialData): bool
    {
        return $user->id === $financialData->client->user_id;
    }
}
