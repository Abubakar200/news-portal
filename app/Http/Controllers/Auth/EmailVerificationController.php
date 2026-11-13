<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(string $locale): View
    {
        return view('auth.verify-email', compact('locale'));
    }

    public function verify(EmailVerificationRequest $request, string $locale): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('site.home', ['locale' => $locale])
            ->with('status', __('Your email address has been verified.'));
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', __('Your email address is already verified.'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', __('A new verification link has been sent to your email address.'));
    }
}
