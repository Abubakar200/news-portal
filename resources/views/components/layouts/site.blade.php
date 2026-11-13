@props([
    'locale',
    'localeSwitchUrl' => null,
    'seoTitle' => null,
    'seoDescription' => null,
    'seoImage' => null,
    'seoType' => 'website',
    'canonicalUrl' => null,
])

@php
    $siteName = config('app.name') === 'Laravel' ? __('News Portal') : config('app.name');
    $localeSwitchUrl ??= route('site.home', ['locale' => $locale === 'ur' ? 'en' : 'ur']);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ur' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle ?? $siteName }} | {{ $siteName }}</title>
    <meta name="description" content="{{ $seoDescription ?? __('Stay up to date with the latest stories and headlines.') }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ $canonicalUrl ?? request()->url() }}">
    <link rel="alternate" hreflang="{{ $locale === 'ur' ? 'en' : 'ur' }}" href="{{ $localeSwitchUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ route('site.home', ['locale' => 'en']) }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $seoTitle ?? $siteName }}">
    <meta property="og:description" content="{{ $seoDescription ?? __('Stay up to date with the latest stories and headlines.') }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? request()->url() }}">
    <meta property="og:locale" content="{{ $locale === 'ur' ? 'ur_PK' : 'en_US' }}">
    @if (! empty($seoImage))
        <meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    @stack('schema')
    <script>
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-slate-900 antialiased transition-colors dark:bg-slate-950 dark:text-slate-100 {{ $locale === 'ur' ? 'font-urdu' : '' }}">
    <div class="border-b border-slate-200 bg-slate-950 text-white dark:border-slate-800">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2 text-xs sm:px-6 lg:px-8">
            <p>{{ now()->locale($locale)->translatedFormat('l, F j, Y') }}</p>
            <div class="flex items-center gap-4">
                <a class="transition hover:text-amber-300" href="{{ $localeSwitchUrl }}">{{ __('Switch language') }}</a>
                <button class="rounded p-1 transition hover:text-amber-300" type="button" data-theme-toggle aria-label="{{ __('Toggle dark mode') }}" title="{{ __('Toggle dark mode') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42M3 12h2m14 0h2M5.64 18.36l1.42-1.42m9.88-9.88 1.42-1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-5 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <a class="flex items-center gap-3 self-start" href="{{ route('site.home', ['locale' => $locale]) }}">
                <span class="grid size-11 place-items-center rounded-xl bg-amber-400 text-xl font-black text-slate-950" aria-hidden="true">N</span>
                <span>
                    <span class="block text-xl font-black tracking-tight">{{ $siteName }}</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">{{ $locale === 'ur' ? 'باخبر رہیں' : 'Stay informed' }}</span>
                </span>
            </a>
            <form class="flex w-full max-w-xl items-center gap-2" method="GET" action="{{ route('site.news.index', ['locale' => $locale]) }}" role="search">
                <label class="sr-only" for="site-search">{{ __('Search news') }}</label>
                <input id="site-search" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-stone-50 px-4 py-3 text-sm outline-none ring-amber-400 transition placeholder:text-slate-400 focus:ring-2 dark:border-slate-700 dark:bg-slate-800" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search news') }}">
                <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Search') }}</button>
            </form>
        </div>
        <nav class="border-t border-slate-100 dark:border-slate-800" aria-label="{{ __('All News') }}">
            <div class="mx-auto flex max-w-7xl items-center gap-6 overflow-x-auto px-4 py-3 text-sm font-semibold sm:px-6 lg:px-8">
                <a class="shrink-0 text-amber-700 dark:text-amber-300" href="{{ route('site.home', ['locale' => $locale]) }}">{{ __('Home') }}</a>
                <a class="shrink-0 transition hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('site.news.index', ['locale' => $locale]) }}">{{ __('All News') }}</a>
                @foreach ($navigationCategories as $navigationCategory)
                    <a class="shrink-0 whitespace-nowrap text-slate-600 transition hover:text-amber-700 dark:text-slate-300 dark:hover:text-amber-300" href="{{ route('site.categories.show', ['locale' => $locale, 'category' => $navigationCategory->slug]) }}">
                        {{ $locale === 'ur' ? $navigationCategory->name_ur : $navigationCategory->name_en }}
                    </a>
                @endforeach
            </div>
        </nav>
        <div class="border-t border-slate-100 dark:border-slate-800">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-5 gap-y-3 px-4 py-3 text-sm sm:px-6 lg:px-8">
                @auth
                    <a class="font-semibold hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('profile.edit', ['locale' => $locale]) }}">{{ __('Profile') }}</a>
                    <a class="font-semibold hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('bookmarks.index', ['locale' => $locale]) }}">{{ __('Bookmarks') }}</a>
                    <a class="font-semibold hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('notifications.index', ['locale' => $locale]) }}">
                        {{ __('Notifications') }}
                        @if ($unreadNotificationCount > 0)
                            <span class="ms-1 rounded-full bg-amber-400 px-2 py-0.5 text-xs text-slate-950">{{ $unreadNotificationCount }}</span>
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout', ['locale' => $locale]) }}">
                        @csrf
                        <button class="font-semibold text-slate-500 hover:text-amber-700 dark:text-slate-400 dark:hover:text-amber-300" type="submit">{{ __('Sign out') }}</button>
                    </form>
                @else
                    <a class="font-semibold hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('login', ['locale' => $locale]) }}">{{ __('Sign in') }}</a>
                    <a class="font-semibold hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('register', ['locale' => $locale]) }}">{{ __('Create account') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:px-8">
            <div>
                <a class="text-xl font-black tracking-tight" href="{{ route('site.home', ['locale' => $locale]) }}">{{ $siteName }}</a>
                <p class="mt-3 max-w-md text-sm leading-7 text-slate-500 dark:text-slate-400">{{ __('Independent journalism for informed communities.') }}</p>
                <form class="mt-5 flex max-w-md flex-col gap-2 sm:flex-row" method="POST" action="{{ route('newsletter.store', ['locale' => $locale]) }}">
                    @csrf
                    <label class="sr-only" for="newsletter-email">{{ __('Email address') }}</label>
                    <input id="newsletter-email" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-stone-50 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Email address') }}" required maxlength="255">
                    <button class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-bold text-slate-950 transition hover:bg-amber-300" type="submit">{{ __('Subscribe') }}</button>
                </form>
                @if (session('status'))
                    <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300" role="status">{{ session('status') }}</p>
                @endif
                @error('email')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <h2 class="text-sm font-bold">{{ __('All News') }}</h2>
                <ul class="mt-4 flex flex-wrap gap-x-5 gap-y-3 text-sm text-slate-500 dark:text-slate-400">
                    @foreach ($navigationCategories as $navigationCategory)
                        <li>
                            <a class="transition hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('site.categories.show', ['locale' => $locale, 'category' => $navigationCategory->slug]) }}">
                                {{ $locale === 'ur' ? $navigationCategory->name_ur : $navigationCategory->name_en }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100 px-4 py-4 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
            &copy; {{ now()->year }} {{ $siteName }}. {{ __('All rights reserved.') }}
        </div>
    </footer>
</body>
</html>
