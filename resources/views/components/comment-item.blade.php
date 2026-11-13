@props(['comment', 'locale'])

<article id="comment-{{ $comment->id }}" class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-bold">{{ $comment->user?->name ?? __('Former member') }}</p>
        <time class="text-xs text-slate-500 dark:text-slate-400" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->locale($locale)->diffForHumans() }}</time>
    </div>
    <p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $comment->body }}</p>
    @auth
        @if ($comment->user_id === auth()->id())
            <div class="mt-3 flex gap-4 text-sm">
                <details>
                    <summary class="cursor-pointer font-semibold text-amber-700 dark:text-amber-300">{{ __('Edit') }}</summary>
                    <form class="mt-3 grid gap-2" method="POST" action="{{ route('comments.update', ['locale' => $locale, 'comment' => $comment]) }}">
                        @csrf
                        @method('PATCH')
                        <label class="sr-only" for="edit-comment-{{ $comment->id }}">{{ __('Edit comment') }}</label>
                        <textarea class="w-full rounded-lg border border-slate-300 bg-white p-3 dark:border-slate-700 dark:bg-slate-900" id="edit-comment-{{ $comment->id }}" name="body" maxlength="3000" required>{{ $comment->body }}</textarea>
                        @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <button class="justify-self-start rounded-lg bg-slate-900 px-3 py-2 font-semibold text-white dark:bg-amber-400 dark:text-slate-950" type="submit">{{ __('Save changes') }}</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('comments.destroy', ['locale' => $locale, 'comment' => $comment]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="font-semibold text-red-600 hover:underline dark:text-red-400" type="submit">{{ __('Delete') }}</button>
                </form>
            </div>
        @endif
    @endauth
</article>
