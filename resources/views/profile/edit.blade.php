<x-account-panel :locale="$locale" :title="__('Your profile')">
    <form class="mt-6 grid gap-5" method="POST" action="{{ route('profile.update', ['locale' => $locale]) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        @if ($user->profile_photo_path)
            <img class="size-20 rounded-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}" alt="{{ __('Profile photo') }}">
        @endif
        <div>
            <label class="mb-1 block text-sm font-semibold" for="name">{{ __('Name') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="email">{{ __('Email address') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="bio">{{ __('Bio') }}</label>
            <textarea class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="bio" name="bio" rows="4" maxlength="500">{{ old('bio', $user->bio) }}</textarea>
            @error('bio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="profile_photo">{{ __('Profile photo') }}</label>
            <input class="block w-full text-sm file:me-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-2 dark:file:bg-slate-800" id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp">
            @error('profile_photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @if ($user->profile_photo_path)
                <label class="mt-3 flex items-center gap-2 text-sm">
                    <input class="rounded border-slate-300 text-amber-500 focus:ring-amber-400" type="checkbox" name="remove_profile_photo" value="1">
                    {{ __('Remove profile photo') }}
                </label>
            @endif
        </div>
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Save profile') }}</button>
    </form>

    <hr class="my-8 border-slate-200 dark:border-slate-800">

    <h2 class="text-lg font-bold">{{ __('Change password') }}</h2>
    <form class="mt-4 grid gap-5" method="POST" action="{{ route('profile.password', ['locale' => $locale]) }}">
        @csrf
        @method('PATCH')
        <div>
            <label class="mb-1 block text-sm font-semibold" for="current_password">{{ __('Current password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="current_password" name="current_password" type="password" required autocomplete="current-password">
            @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="new_password">{{ __('New password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="new_password" name="password" type="password" required autocomplete="new-password">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="password_confirmation">{{ __('Confirm password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Change password') }}</button>
    </form>
</x-account-panel>
