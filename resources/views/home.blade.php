<x-layouts.site :locale="$locale" :locale-switch-url="$localeSwitchUrl" :seo-title="$seoTitle" :seo-description="$seoDescription" :canonical-url="$canonicalUrl">
    @php($siteName = config('app.name') === 'Laravel' ? __('News Portal') : config('app.name'))

    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        @if ($breakingPosts->isNotEmpty())
            <section class="flex flex-col gap-3 rounded-xl bg-amber-400 px-4 py-3 text-slate-950 sm:flex-row sm:items-center" aria-label="{{ __('Breaking') }}">
                <span class="w-fit shrink-0 rounded bg-slate-950 px-3 py-1 text-xs font-black uppercase tracking-wider text-white">{{ __('Breaking') }}</span>
                <div class="flex min-w-0 flex-wrap gap-x-5 gap-y-2 text-sm font-semibold">
                    @foreach ($breakingPosts as $breakingPost)
                        @php($breakingTranslation = $breakingPost->translations->first())
                        @if ($breakingTranslation)
                            <a class="line-clamp-1 underline decoration-slate-950/30 underline-offset-4 hover:decoration-slate-950" href="{{ route('site.news.show', ['locale' => $locale, 'slug' => $breakingTranslation->slug]) }}">
                                {{ $breakingTranslation->title }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <section class="py-8 sm:py-10" aria-labelledby="top-story-heading">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">{{ $siteName }}</p>
                    <h1 id="top-story-heading" class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">{{ __('Top story') }}</h1>
                </div>
                <a class="text-sm font-bold text-amber-700 hover:text-amber-800 dark:text-amber-300 dark:hover:text-amber-200" href="{{ route('site.news.index', ['locale' => $locale]) }}">{{ __('View all') }} <span aria-hidden="true">&rarr;</span></a>
            </div>
            @if ($featuredPost)
                <x-news-card :post="$featuredPost" :locale="$locale" :featured="true" />
            @else
                <p class="rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('No featured stories yet.') }}</p>
            @endif
        </section>

        <section class="grid gap-10 border-t border-slate-200 py-10 dark:border-slate-800 lg:grid-cols-[minmax(0,1fr)_20rem]" aria-labelledby="latest-heading">
            <div>
                <div class="mb-5 flex items-end justify-between gap-4">
                    <h2 id="latest-heading" class="text-2xl font-black tracking-tight">{{ __('Latest News') }}</h2>
                    <a class="text-sm font-bold text-amber-700 hover:text-amber-800 dark:text-amber-300" href="{{ route('site.news.index', ['locale' => $locale]) }}">{{ __('View all') }} <span aria-hidden="true">&rarr;</span></a>
                </div>
                @if ($latestPosts->isNotEmpty())
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($latestPosts as $post)
                            <x-news-card :post="$post" :locale="$locale" />
                        @endforeach
                    </div>
                @else
                    <p class="rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('No news available yet.') }}</p>
                @endif
            </div>

            <aside aria-labelledby="popular-heading">
                <div class="mb-5 flex items-end justify-between">
                    <h2 id="popular-heading" class="text-xl font-black tracking-tight">{{ __('Popular News') }}</h2>
                </div>
                @if ($popularPosts->isNotEmpty())
                    <ol class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($popularPosts as $index => $post)
                            @php($translation = $post->translations->first())
                            @if ($translation)
                                <li class="flex gap-4 py-4 first:pt-0">
                                    <span class="text-3xl font-black leading-none text-amber-500/70">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <a class="font-bold leading-6 hover:text-amber-700 dark:hover:text-amber-300" href="{{ route('site.news.show', ['locale' => $locale, 'slug' => $translation->slug]) }}">{{ $translation->title }}</a>
                                        @if ($post->published_at)
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $post->published_at->locale($locale)->translatedFormat('M j, Y') }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No news available yet.') }}</p>
                @endif
            </aside>
        </section>

        @foreach ($categoryNews as $category)
            @php($categoryName = $locale === 'ur' ? $category->name_ur : $category->name_en)
            <section class="border-t border-slate-200 py-10 dark:border-slate-800" aria-labelledby="category-{{ $category->id }}">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <h2 id="category-{{ $category->id }}" class="text-2xl font-black tracking-tight">{{ $categoryName }}</h2>
                    <a class="shrink-0 text-sm font-bold text-amber-700 hover:text-amber-800 dark:text-amber-300" href="{{ route('site.categories.show', ['locale' => $locale, 'category' => $category->slug]) }}">{{ __('View all') }} <span aria-hidden="true">&rarr;</span></a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($category->posts as $post)
                        <x-news-card :post="$post" :locale="$locale" />
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.site>
