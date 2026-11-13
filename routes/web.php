<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');

Route::prefix('{locale}')
    ->where(['locale' => 'en|ur'])
    ->middleware('news.locale')
    ->group(function (): void {
        Route::name('site.')->group(function (): void {
            Route::get('/', [PublicNewsController::class, 'home'])->name('home');
            Route::get('/news', [PublicNewsController::class, 'index'])->name('news.index');
            Route::get('/news/{slug}', [PublicNewsController::class, 'show'])->name('news.show');
            Route::get('/category/{category:slug}', [PublicNewsController::class, 'category'])->name('categories.show');
            Route::get('/tag/{tag:slug}', [PublicNewsController::class, 'tag'])->name('tags.show');
            Route::get('/authors/{author}/{slug}', [PublicNewsController::class, 'author'])
                ->whereNumber('author')
                ->name('authors.show');
        });

        Route::middleware('guest')->group(function (): void {
            Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('throttle:registration')
                ->name('register.store');
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('throttle:login')
                ->name('login.store');
            Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('throttle:password-reset')
                ->name('password.email');
            Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
            Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
        });

        Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
            ->middleware('auth')
            ->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->whereNumber('id')
            ->middleware(['auth', 'signed'])
            ->name('verification.verify');
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware(['auth', 'throttle:verification'])
            ->name('verification.send');

        Route::middleware('auth')->group(function (): void {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

            Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
            Route::post('/bookmarks/{post}', [BookmarkController::class, 'store'])
                ->whereNumber('post')
                ->name('bookmarks.store');
            Route::delete('/bookmarks/{post}', [BookmarkController::class, 'destroy'])
                ->whereNumber('post')
                ->name('bookmarks.destroy');

            Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
            Route::patch('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])
                ->name('notifications.read-all');
            Route::patch('/notifications/{notification}', [UserNotificationController::class, 'markRead'])
                ->whereUuid('notification')
                ->name('notifications.read');

            Route::post('/news/{slug}/comments', [CommentController::class, 'store'])
                ->middleware('throttle:comments')
                ->name('comments.store');
            Route::patch('/comments/{comment}', [CommentController::class, 'update'])
                ->whereNumber('comment')
                ->middleware('throttle:comments')
                ->name('comments.update');
            Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
                ->whereNumber('comment')
                ->name('comments.destroy');
        });

        Route::post('/newsletter', NewsletterController::class)
            ->middleware('throttle:newsletter')
            ->name('newsletter.store');
    });
