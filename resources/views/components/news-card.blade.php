@php($translation = $post->translations->first())
@if ($translation)
    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
        <a class="block overflow-hidden" href="{{ route('site.news.show', ['locale' => $locale, 'slug' => $translation->slug]) }}">
            @if ($post->featured_image)
                <img class="{{ $featured ? 'aspect-[16/8] sm:aspect-[16/7]' : 'aspect-[16/10]' }} w-full object-cover transition duration-500 group-hover:scale-[1.02]" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" alt="{{ $translation->image_alt ?: $translation->title }}" loading="{{ $featured ? 'eager' : 'lazy' }}" fetchpriority="{{ $featured ? 'high' : 'auto' }}" decoding="async">
            @else
                <div class="{{ $featured ? 'aspect-[16/8] sm:aspect-[16/7]' : 'aspect-[16/10]' }} grid w-full place-items-center bg-gradient-to-br from-slate-200 via-stone-100 to-amber-100 text-4xl font-black text-slate-300 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800 dark:text-slate-700" aria-hidden="true">N</div>
            @endif
        </a>
        <div class="{{ $featured ? 'p-6 sm:p-8' : 'p-5' }}">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs font-semibold">
                @if ($post->category)
                    <a class="text-amber-700 hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-200" href="{{ route('site.categories.show', ['locale' => $locale, 'category' => $post->category->slug]) }}">
                        {{ $locale === 'ur' ? $post->category->name_ur : $post->category->name_en }}
                    </a>
                @endif
                @if ($post->is_featured)
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-900 dark:bg-amber-400/15 dark:text-amber-200">{{ __('Featured') }}</span>
                @endif
                @if ($post->published_at)
                    <time class="text-slate-500 dark:text-slate-400" datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->locale($locale)->translatedFormat('M j, Y') }}</time>
                @endif
            </div>
            <h2 class="{{ $featured ? 'mt-4 text-2xl sm:text-4xl' : 'mt-3 text-lg' }} font-black leading-tight tracking-tight">
                <a class="transition group-hover:text-amber-800 dark:group-hover:text-amber-300" href="{{ route('site.news.show', ['locale' => $locale, 'slug' => $translation->slug]) }}">{{ $translation->title }}</a>
            </h2>
            @if ($translation->excerpt)
                <p class="{{ $featured ? 'mt-4 text-base sm:text-lg sm:leading-8' : 'mt-3 text-sm leading-6' }} line-clamp-3 text-slate-600 dark:text-slate-400">{{ $translation->excerpt }}</p>
            @endif
            @if ($post->author)
                <a class="mt-4 inline-block text-xs font-semibold text-slate-500 hover:text-amber-700 dark:text-slate-400 dark:hover:text-amber-300" href="{{ route('site.authors.show', ['locale' => $locale, 'author' => $post->author, 'slug' => \Illuminate\Support\Str::slug($post->author->name) ?: 'author-'.$post->author->id]) }}">
                    {{ __('By :author', ['author' => $post->author->name]) }}
                </a>
            @endif
        </div>
    </article>
@endif