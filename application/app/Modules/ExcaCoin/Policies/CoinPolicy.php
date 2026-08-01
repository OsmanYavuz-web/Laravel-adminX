<?php

namespace App\Policies;

use App\Models\Coin;
use App\Models\User;

class CoinPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('coins.view');
    }

    public function view(User $user, Coin $coin): bool
    {
        return $user->can('coins.view');
    }

    public function create(User $user): bool
    {
        return $user->can('coins.create');
    }

    public function update(User $user, Coin $coin): bool
    {
        return $user->can('coins.update');
    }

    public function delete(User $user, Coin $coin): bool
    {
        return $user->can('coins.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('coins.export');
    }
}
