<x-layouts.site :locale="$locale" :seo-title="__('Notifications')">
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-3xl font-black tracking-tight">{{ __('Notifications') }}</h1>
            @if ($notifications->contains(fn ($notification): bool => $notification->unread()))
                <form method="POST" action="{{ route('notifications.read-all', ['locale' => $locale]) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-sm font-semibold text-amber-700 hover:underline dark:text-amber-300" type="submit">{{ __('Mark all as read') }}</button>
                </form>
            @endif
        </div>
        @if (session('status'))
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</p>
        @endif
        <div class="mt-6 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            @forelse ($notifications as $notification)
                <article class="flex flex-wrap items-center justify-between gap-4 p-4 {{ $notification->unread() ? 'bg-amber-50/60 dark:bg-amber-950/20' : '' }}">
                    <div>
                        <p class="font-semibold">
                            @switch($notification->data['type'] ?? '')
                                @case('comment_reply')
                                    {{ __(':name replied to your comment.', ['name' => $notification->data['author_name'] ?? __('A reader')]) }}
                                    @break
                                @case('comment_status_approved')
                                    {{ __('Your comment was approved.') }}
                                    @break
                                @case('comment_status_rejected')
                                    {{ __('Your comment was not approved.') }}
                                    @break
                                @default
                                    {{ __('You have a new update.') }}
                            @endswitch
                        </p>
                        <time class="mt-1 block text-xs text-slate-500 dark:text-slate-400" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->locale($locale)->diffForHumans() }}</time>
                    </div>
                    @if ($notification->unread())
                        <form method="POST" action="{{ route('notifications.read', ['locale' => $locale, 'notification' => $notification->id]) }}">
                            @csrf
                            @method('PATCH')
                            <button class="text-sm font-semibold text-amber-700 hover:underline dark:text-amber-300" type="submit">{{ __('Mark as read') }}</button>
                        </form>
                    @elseif (! empty($notification->data['url']))
                        <a class="text-sm font-semibold text-amber-700 hover:underline dark:text-amber-300" href="{{ $notification->data['url'] }}">{{ __('View') }}</a>
                    @endif
                </article>
            @empty
                <p class="p-8 text-center text-slate-500 dark:text-slate-400">{{ __('You have no notifications.') }}</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $notifications->links('pagination::tailwind') }}</div>
    </section>
</x-layouts.site>
