<x-account-panel :locale="$locale" :title="__('Reset password')">
    <form class="mt-6 grid gap-5" method="POST" action="{{ route('password.update', ['locale' => $locale]) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="mb-1 block text-sm font-semibold" for="email">{{ __('Email address') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="password">{{ __('New password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="password" name="password" type="password" required autocomplete="new-password">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="password_confirmation">{{ __('Confirm password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Reset password') }}</button>
    </form>
</x-account-panel>
