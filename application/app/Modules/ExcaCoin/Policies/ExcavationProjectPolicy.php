<?php

namespace App\Modules\ExcaCoin\Policies;

use App\Models\User;
use App\Modules\ExcaCoin\Models\ExcavationProject;

class ExcavationProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('excavation_projects.view');
    }

    public function view(User $user, ExcavationProject $excavationProject): bool
    {
        return $user->can('excavation_projects.view');
    }

    public function create(User $user): bool
    {
        return $user->can('excavation_projects.create');
    }

    public function update(User $user, ExcavationProject $excavationProject): bool
    {
        return $user->can('excavation_projects.update');
    }

    public function delete(User $user, ExcavationProject $excavationProject): bool
    {
        return $user->can('excavation_projects.delete');
    }
}
