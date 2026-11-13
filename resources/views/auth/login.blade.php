<x-account-panel :locale="$locale" :title="__('Sign in')">
    <form class="mt-6 grid gap-5" method="POST" action="{{ route('login.store', ['locale' => $locale]) }}">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-semibold" for="email">{{ __('Email address') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold" for="password">{{ __('Password') }}</label>
            <input class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input class="rounded border-slate-300 text-amber-500 focus:ring-amber-400" type="checkbox" name="remember" value="1">
            {{ __('Remember me') }}
        </label>
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Sign in') }}</button>
        <div class="flex flex-wrap justify-between gap-3 text-sm">
            <a class="font-semibold text-amber-700 hover:underline dark:text-amber-300" href="{{ route('password.request', ['locale' => $locale]) }}">{{ __('Forgot your password?') }}</a>
            <a class="font-semibold text-amber-700 hover:underline dark:text-amber-300" href="{{ route('register', ['locale' => $locale]) }}">{{ __('Create account') }}</a>
        </div>
    </form>
</x-account-panel>
