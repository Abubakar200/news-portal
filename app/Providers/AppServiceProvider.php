<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (?User $user): ?bool => $user?->hasRole('super-admin') ? true : null);

        RateLimiter::for('login', function (Request $request): Limit {
            $email = $request->string('email')->lower()->toString();

            return Limit::perMinute(5)->by(Str::transliterate($email.'|'.$request->ip()));
        });
        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('password-reset', function (Request $request): Limit {
            $email = $request->string('email')->lower()->toString();

            return Limit::perMinute(3)->by(Str::transliterate($email.'|'.$request->ip()));
        });
        RateLimiter::for('verification', fn (Request $request): Limit => Limit::perMinute(3)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('comments', fn (Request $request): Limit => Limit::perMinute(5)->by($request->user()->getAuthIdentifier().'|'.$request->ip()));
        RateLimiter::for('newsletter', fn (Request $request): Limit => Limit::perMinute(3)->by($request->ip()));

        ViewFacade::composer('components.layouts.site', function (View $view): void {
            $locale = app()->getLocale();

            $navigationCategories = Category::query()
                ->where('is_active', true)
                ->whereHas('posts', fn (Builder $query): Builder => $query->publishedForLocale($locale))
                ->orderBy('name_en')
                ->limit(7)
                ->get();

            $view->with([
                'navigationCategories' => $navigationCategories,
                'unreadNotificationCount' => auth()->check()
                    ? auth()->user()->unreadNotifications()->count()
                    : 0,
            ]);
        });
    }
}
