<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = mb_strtolower(trim($validated['email']));
        $subscriber = NewsletterSubscriber::withTrashed()->firstOrCreate(
            ['email' => $email],
            ['is_active' => true, 'subscribed_at' => now()],
        );

        $wasTrashed = $subscriber->trashed();

        if ($wasTrashed) {
            $subscriber->restore();
        }

        if ($wasTrashed || ! $subscriber->is_active) {
            $subscriber->update([
                'is_active' => true,
                'subscribed_at' => now(),
            ]);
        }

        return back()->with('status', __('Thank you for subscribing to the newsletter.'));
    }
}
