<x-account-panel :locale="$locale" :title="__('Forgot your password?')" :description="__('Enter your email address and we will send password reset instructions if an account exists.')">
    <form class="mt-6 grid gap-5" method="POST" action="{{ route('password.email', ['locale' => $locale]) }}">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold" for="email">{{ __('Email address') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Send reset link') }}</button>
    </form>
</x-account-panel>
