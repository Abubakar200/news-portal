<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(string $locale): View
    {
        return view('auth.forgot-password', compact('locale'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);
        $status = Password::sendResetLink($validated);

        if ($status === Password::ResetThrottled) {
            return back()->withErrors([
                'email' => __('Please wait before requesting another password reset link.'),
            ]);
        }

        return back()->with('status', __('If the address belongs to an account, password reset instructions will be sent.'));
    }
}
