<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $locale, string $token): View
    {
        return view('auth.reset-password', [
            'locale' => $locale,
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->mixedCase()->numbers()->symbols()],
        ]);
        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PasswordReset) {
            return redirect()->route('login', ['locale' => $locale])
                ->with('status', __('Your password has been reset.'));
        }

        return back()->withErrors([
            'email' => __('The password reset link is invalid or has expired.'),
        ]);
    }
}
