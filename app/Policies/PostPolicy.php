<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('posts.create')
            || $user->can('posts.update')
            || $user->can('posts.update-own');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('posts.update')
            || $post->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('posts.update')
            || ($user->can('posts.update-own') && $post->author_id === $user->id);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('posts.delete')
            || ($user->can('posts.delete-own') && $post->author_id === $user->id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('posts.delete');
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->can('posts.delete')
            || ($user->can('posts.delete-own') && $post->author_id === $user->id);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('posts.delete');
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->can('posts.publish');
    }

    public function schedule(User $user, Post $post): bool
    {
        return $user->can('posts.schedule');
    }

    public function draft(User $user, Post $post): bool
    {
        return $user->can('posts.update')
            || ($user->can('posts.update-own') && $post->author_id === $user->id);
    }
}
