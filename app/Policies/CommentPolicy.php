<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canModerate($user);
    }

    public function view(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $this->canModerate($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $this->canModerate($user);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $this->canModerate($user);
    }

    public function restore(User $user, Comment $comment): bool
    {
        return $this->canModerate($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->canModerate($user);
    }

    private function canModerate(User $user): bool
    {
        return $user->can('posts.review') || $user->can('content.manage');
    }
}
