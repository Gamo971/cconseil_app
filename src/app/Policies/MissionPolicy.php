<?php

namespace App\Policies;

use App\Models\Mission;
use App\Models\User;

class MissionPolicy
{
    public function view(User $user, Mission $mission): bool
    {
        return $user->id === $mission->client->user_id;
    }

    public function update(User $user, Mission $mission): bool
    {
        return $user->id === $mission->client->user_id;
    }

    public function delete(User $user, Mission $mission): bool
    {
        return $user->id === $mission->client->user_id;
    }
}
