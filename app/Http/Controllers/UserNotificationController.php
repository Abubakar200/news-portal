<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('locale', 'notifications'));
    }

    public function markRead(Request $request, string $locale, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back();
    }

    public function markAllRead(Request $request, string $locale): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', __('All notifications have been marked as read.'));
    }
}
