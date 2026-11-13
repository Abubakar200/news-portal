<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function view(User $user, User $author): bool
    {
        return $user->can('users.manage') && $author->hasRole('author');
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $author): bool
    {
        return $user->can('users.manage') && $author->hasRole('author');
    }

    public function delete(User $user, User $author): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $author): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, User $author): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }
}
