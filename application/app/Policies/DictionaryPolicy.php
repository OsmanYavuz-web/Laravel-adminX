<?php

namespace App\Policies;

use App\Models\Dictionary;
use App\Models\User;

class DictionaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dictionaries.view');
    }

    public function view(User $user, Dictionary $dictionary): bool
    {
        return $user->can('dictionaries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dictionaries.create');
    }

    public function update(User $user, Dictionary $dictionary): bool
    {
        return $user->can('dictionaries.update');
    }

    public function delete(User $user, Dictionary $dictionary): bool
    {
        return $user->can('dictionaries.delete');
    }
}
