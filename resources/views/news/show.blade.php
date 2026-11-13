<x-layouts.site :locale="$locale" :locale-switch-url="$localeSwitchUrl" :seo-title="$seoTitle" :seo-description="$seoDescription" :seo-image="$seoImage" seo-type="article" :canonical-url="$canonicalUrl">
    @push('schema')
        <script type="application/ld+json">@json($schema)</script>
    @endpush

    <article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8" itemscope itemtype="https://schema.org/NewsArticle">
        @if ($post->category)
            <a class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700 hover:text-amber-800 dark:text-amber-300" href="{{ route('site.categories.show', ['locale' => $locale, 'category' => $post->category->slug]) }}">
                {{ $locale === 'ur' ? $post->category->name_ur : $post->category->name_en }}
            </a>
        @endif
        <h1 class="mt-4 text-3xl font-black leading-tight tracking-tight sm:text-5xl" itemprop="headline">{{ $translation->title }}</h1>
        @if ($translation->excerpt)
            <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300" itemprop="description">{{ $translation->excerpt }}</p>
        @endif

        <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 border-y border-slate-200 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
            @if ($post->author)
                <a class="font-semibold text-slate-800 hover:text-amber-700 dark:text-slate-200 dark:hover:text-amber-300" href="{{ route('site.authors.show', ['locale' => $locale, 'author' => $post->author, 'slug' => \Illuminate\Support\Str::slug($post->author->name) ?: 'author-'.$post->author->id]) }}">
                    {{ __('By :author', ['author' => $post->author->name]) }}
                </a>
            @endif
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toIso8601String() }}" itemprop="datePublished">{{ __('Published') }} {{ $post->published_at->locale($locale)->translatedFormat('M j, Y') }}</time>
            @endif
            <span>{{ __('Views') }}: {{ number_format($post->view_count) }}</span>
        </div>

        @if ($seoImage)
            <figure class="mt-8 overflow-hidden rounded-2xl bg-slate-200 dark:bg-slate-800">
                <img class="max-h-[34rem] w-full object-cover" src="{{ $seoImage }}" alt="{{ $translation->image_alt ?: $translation->title }}" fetchpriority="high" itemprop="image">
            </figure>
        @endif

        <div class="article-body mt-8 text-lg leading-8 {{ $locale === 'ur' ? 'font-urdu' : '' }}" itemprop="articleBody">
            {!! $articleBody !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2" aria-label="{{ __('Tags') }}">
                @foreach ($post->tags as $tag)
                    <a class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:bg-amber-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" href="{{ route('site.tags.show', ['locale' => $locale, 'tag' => $tag->slug]) }}">
                        #{{ $locale === 'ur' ? $tag->name_ur : $tag->name_en }}
                    </a>
                @endforeach
            </div>
        @endif

        <section class="mt-10 border-y border-slate-200 py-5 dark:border-slate-800" aria-labelledby="share-heading">
            <h2 id="share-heading" class="text-sm font-bold">{{ __('Share this story') }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                <a class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-800" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener noreferrer">{{ __('Facebook') }}</a>
                <a class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800" href="https://wa.me/?text={{ urlencode($translation->title.' '.$canonicalUrl) }}" target="_blank" rel="noopener noreferrer">{{ __('WhatsApp') }}</a>
                <a class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" href="https://twitter.com/intent/tweet?url={{ urlencode($canonicalUrl) }}&text={{ urlencode($translation->title) }}" target="_blank" rel="noopener noreferrer">{{ __('X') }}</a>
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:border-amber-400 dark:border-slate-700" type="button" data-copy-link="{{ $canonicalUrl }}" data-copy-success="{{ __('Link copied') }}" data-copy-failure="{{ __('Could not copy the link.') }}">{{ __('Copy link') }}</button>
            </div>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400" data-copy-status aria-live="polite"></p>
        </section>

        <section class="mt-6" aria-label="{{ __('Bookmarks') }}">
            @auth
                @if ($isBookmarked)
                    <form method="POST" action="{{ route('bookmarks.destroy', ['locale' => $locale, 'post' => $post]) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:border-amber-400 dark:border-slate-700" type="submit">{{ __('Remove bookmark') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('bookmarks.store', ['locale' => $locale, 'post' => $post]) }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold hover:border-amber-400 dark:border-slate-700" type="submit">{{ __('Save article') }}</button>
                    </form>
                @endif
            @else
                <a class="text-sm font-semibold text-amber-700 hover:underline dark:text-amber-300" href="{{ route('login', ['locale' => $locale]) }}">{{ __('Sign in to save this article') }}</a>
            @endauth
        </section>
    </article>

    <section class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8" aria-labelledby="comments-heading">
        <div class="flex flex-wrap items-baseline gap-3">
            <h2 id="comments-heading" class="text-2xl font-black">{{ __('Comments') }}</h2>
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $comments->total() }}</span>
        </div>
        @if (session('status'))
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</p>
        @endif

        @auth
            <form class="mt-5 grid gap-3" method="POST" action="{{ route('comments.store', ['locale' => $locale, 'slug' => $translation->slug]) }}">
                @csrf
                <label class="font-semibold" for="comment-body">{{ __('Leave a comment') }}</label>
                <textarea class="w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-700 dark:bg-slate-900" id="comment-body" name="body" rows="4" maxlength="3000" required>{{ old('body') }}</textarea>
                @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button class="justify-self-start rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white dark:bg-amber-400 dark:text-slate-950" type="submit">{{ __('Submit comment') }}</button>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Comments are reviewed before they appear publicly.') }}</p>
            </form>
        @else
            <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">
                <a class="font-semibold text-amber-700 hover:underline dark:text-amber-300" href="{{ route('login', ['locale' => $locale]) }}">{{ __('Sign in') }}</a>
                {{ __('to join the conversation.') }}
            </p>
        @endauth

        <div class="mt-8 grid gap-4">
            @forelse ($comments as $comment)
                <x-comment-item :comment="$comment" :locale="$locale" />
                @foreach ($comment->replies as $reply)
                    <div class="ms-5 sm:ms-10">
                        <x-comment-item :comment="$reply" :locale="$locale" />
                    </div>
                @endforeach
                @auth
                    <form class="ms-5 grid gap-3 sm:ms-10" method="POST" action="{{ route('comments.store', ['locale' => $locale, 'slug' => $translation->slug]) }}">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <label class="font-semibold" for="reply-{{ $comment->id }}">{{ __('Reply') }}</label>
                        <textarea class="w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-700 dark:bg-slate-900" id="reply-{{ $comment->id }}" name="body" rows="2" maxlength="3000" required></textarea>
                        @error('parent_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <button class="justify-self-start rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold hover:border-amber-400 dark:border-slate-700" type="submit">{{ __('Reply') }}</button>
                    </form>
                @endauth
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('No comments yet.') }}</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $comments->links('pagination::tailwind') }}</div>
    </section>

    @if ($relatedPosts->isNotEmpty())
        <section class="mx-auto max-w-7xl border-t border-slate-200 px-4 py-10 sm:px-6 lg:px-8 dark:border-slate-800" aria-labelledby="related-heading">
            <h2 id="related-heading" class="mb-5 text-2xl font-black tracking-tight">{{ __('Related Stories') }}</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($relatedPosts as $relatedPost)
                    <x-news-card :post="$relatedPost" :locale="$locale" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
