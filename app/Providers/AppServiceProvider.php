<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
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

        ViewFacade::composer('components.layouts.site', function (View $view): void {
            $locale = app()->getLocale();

            $navigationCategories = Category::query()
                ->where('is_active', true)
                ->whereHas('posts', fn (Builder $query): Builder => $query->publishedForLocale($locale))
                ->orderBy('name_en')
                ->limit(7)
                ->get();

            $view->with('navigationCategories', $navigationCategories);
        });
    }
}
