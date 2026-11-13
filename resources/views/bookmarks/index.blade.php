<x-layouts.site :locale="$locale" :seo-title="__('Bookmarks')">
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-black tracking-tight">{{ __('Bookmarks') }}</h1>
        @if (session('status'))
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</p>
        @endif
        @if ($posts->isEmpty())
            <p class="mt-6 rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('You have not saved any articles yet.') }}</p>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <div class="flex flex-col gap-3">
                        <x-news-card :post="$post" :locale="$locale" />
                        <form method="POST" action="{{ route('bookmarks.destroy', ['locale' => $locale, 'post' => $post]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold hover:border-red-400 hover:text-red-600 dark:border-slate-700" type="submit">{{ __('Remove bookmark') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $posts->links('pagination::tailwind') }}</div>
        @endif
    </section>
</x-layouts.site>
