<?php

namespace App\Modules\ExcaCoin\Policies;

use App\Models\User;
use App\Modules\ExcaCoin\Models\Find;

class FindPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('finds.view');
    }

    public function view(User $user, Find $find): bool
    {
        return $user->can('finds.view');
    }

    public function create(User $user): bool
    {
        return $user->can('finds.create');
    }

    public function update(User $user, Find $find): bool
    {
        return $user->can('finds.update');
    }

    public function delete(User $user, Find $find): bool
    {
        return $user->can('finds.delete');
    }
}
