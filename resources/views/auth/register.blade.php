<x-account-panel :locale="$locale" :title="__('Create account')">
    <form class="mt-6 grid gap-5" method="POST" action="{{ route('register.store', ['locale' => $locale]) }}">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold" for="name">{{ __('Name') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="email">{{ __('Email address') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="password">{{ __('Password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="password" name="password" type="password" required autocomplete="new-password">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="password_confirmation">{{ __('Confirm password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Create account') }}</button>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Already have an account?') }}
            <a class="font-semibold text-amber-700 hover:underline dark:text-amber-300" href="{{ route('login', ['locale' => $locale]) }}">{{ __('Sign in') }}</a>
        </p>
    </form>
</x-account-panel>
