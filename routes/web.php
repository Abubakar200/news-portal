<?php

use App\Http\Controllers\PublicNewsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');

Route::prefix('{locale}')
    ->where(['locale' => 'en|ur'])
    ->middleware('news.locale')
    ->name('site.')
    ->group(function (): void {
        Route::get('/', [PublicNewsController::class, 'home'])->name('home');
        Route::get('/news', [PublicNewsController::class, 'index'])->name('news.index');
        Route::get('/news/{slug}', [PublicNewsController::class, 'show'])->name('news.show');
        Route::get('/category/{category:slug}', [PublicNewsController::class, 'category'])->name('categories.show');
        Route::get('/tag/{tag:slug}', [PublicNewsController::class, 'tag'])->name('tags.show');
        Route::get('/authors/{author}/{slug}', [PublicNewsController::class, 'author'])
            ->whereNumber('author')
            ->name('authors.show');
    });
