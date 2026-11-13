<x-layouts.site :locale="$locale" :locale-switch-url="$localeSwitchUrl" :seo-title="$seoTitle" :seo-description="$seoDescription" :canonical-url="$canonicalUrl">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-2xl bg-slate-900 px-6 py-8 text-white sm:px-10 dark:bg-slate-800">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">{{ __('Articles by :author', ['author' => $author->name]) }}</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">{{ $author->name }}</h1>
        </header>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <x-news-card :post="$post" :locale="$locale" />
            @endforeach
        </div>
        <div class="mt-10">
            {{ $posts->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    </div>
</x-layouts.site>
