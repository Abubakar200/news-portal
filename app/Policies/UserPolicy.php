<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->can('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->can('users.manage');
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->can('users.manage') && $user->isNot($targetUser);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function forceDelete(User $user, User $targetUser): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, User $targetUser): bool
    {
        return $user->can('users.manage');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('users.manage');
    }
}
