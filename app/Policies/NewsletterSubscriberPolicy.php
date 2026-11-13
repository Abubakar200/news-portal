<?php

namespace App\Policies;

use App\Models\NewsletterSubscriber;
use App\Models\User;

class NewsletterSubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.manage');
    }

    public function view(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->can('services.manage');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->can('services.manage');
    }

    public function delete(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->can('services.manage');
    }

    public function restore(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->can('services.manage');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('services.manage');
    }
}
