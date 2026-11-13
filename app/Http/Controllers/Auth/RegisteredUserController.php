<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(string $locale): View
    {
        return view('auth.register', compact('locale'));
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::query()->create($validated);

        Auth::login($user);
        event(new Registered($user));

        return redirect()->route('verification.notice', ['locale' => $locale]);
    }
}
