@props(['locale', 'title', 'description' => null])

<x-layouts.site :locale="$locale" :seo-title="$title">
    <section class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <h1 class="text-2xl font-black tracking-tight">{{ $title }}</h1>
            @if ($description)
                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
            @if (session('status'))
                <p class="mt-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</p>
            @endif
            {{ $slot }}
        </div>
    </section>
</x-layouts.site>
