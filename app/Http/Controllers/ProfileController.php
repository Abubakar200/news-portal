<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request, string $locale): View
    {
        return view('profile.edit', [
            'locale' => $locale,
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request, string $locale): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'bio' => ['nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['sometimes', 'boolean'],
        ]);
        $oldPhotoPath = $user->profile_photo_path;
        $emailChanged = $user->email !== $validated['email'];

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'bio' => $validated['bio'] ?? null,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('profile_photo')) {
            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        } elseif ($request->boolean('remove_profile_photo')) {
            $user->profile_photo_path = null;
        }

        $user->save();

        if ($oldPhotoPath !== $user->profile_photo_path && $oldPhotoPath !== null) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice', ['locale' => $locale])
                ->with('status', __('Your email changed. Verify your new address to confirm it.'));
        }

        return back()->with('status', __('Your profile has been updated.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', __('Your password has been changed.'));
    }
}
