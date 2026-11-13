<x-layouts.site :locale="$locale" :locale-switch-url="$localeSwitchUrl" :seo-title="$seoTitle" :seo-description="$seoDescription" :canonical-url="$canonicalUrl">
    @php($siteName = config('app.name') === 'Laravel' ? __('News Portal') : config('app.name'))

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="mb-8 border-b border-slate-200 pb-6 dark:border-slate-800">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">{{ $siteName }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 text-slate-500 dark:text-slate-400">{{ $description }}</p>
        </header>

        @if ($search !== '')
            <form class="mb-8 flex max-w-xl gap-2" method="GET" action="{{ route('site.news.index', ['locale' => $locale]) }}" role="search">
                <label class="sr-only" for="results-search">{{ __('Search news') }}</label>
                <input id="results-search" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-amber-400 dark:border-slate-700 dark:bg-slate-900" name="q" type="search" value="{{ $search }}">
                <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-amber-400 dark:text-slate-950" type="submit">{{ __('Search') }}</button>
            </form>
        @endif

        @if ($posts->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-news-card :post="$post" :locale="$locale" />
                @endforeach
            </div>
            <div class="mt-10">
                {{ $posts->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        @else
            <p class="rounded-2xl border border-dashed border-slate-300 px-6 py-12 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('No stories found.') }}</p>
        @endif
    </div>
</x-layouts.site>
