<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $posts = $request->user()->bookmarkedPosts()
            ->publishedForLocale($locale)
            ->with([
                'translations' => fn (HasMany $translations): HasMany => $translations->where('locale', $locale),
                'author',
                'category',
            ])
            ->paginate(12);

        return view('bookmarks.index', compact('locale', 'posts'));
    }

    public function store(Request $request, string $locale, Post $post): RedirectResponse
    {
        abort_unless(
            Post::query()->publishedForLocale($locale)->whereKey($post->getKey())->exists(),
            404,
        );

        $request->user()->bookmarkedPosts()->syncWithoutDetaching([$post->getKey()]);

        return back()->with('status', __('Article saved to your bookmarks.'));
    }

    public function destroy(Request $request, string $locale, Post $post): RedirectResponse
    {
        $request->user()->bookmarkedPosts()->detach($post->getKey());

        return back()->with('status', __('Article removed from your bookmarks.'));
    }
}
