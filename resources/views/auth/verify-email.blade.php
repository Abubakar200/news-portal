<x-account-panel :locale="$locale" :title="__('Verify your email address')" :description="__('Please verify your email address before continuing. Use the link sent to your inbox.')">
    <form class="mt-6" method="POST" action="{{ route('verification.send', ['locale' => $locale]) }}">
        @csrf
        <button class="rounded-lg bg-slate-900 px-4 py-3 font-bold text-white hover:bg-slate-700 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit">{{ __('Resend verification email') }}</button>
    </form>
</x-account-panel>
