<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\PostTranslation;
use App\Notifications\CommentRepliedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, string $locale, string $slug): RedirectResponse
    {
        $translation = PostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('post', fn (Builder $query): Builder => $query->publishedForLocale($locale))
            ->with('post')
            ->firstOrFail();
        $post = $translation->post;
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(
                    fn (QueryBuilder $query): QueryBuilder => $query
                        ->where('post_id', $post->getKey())
                        ->where('status', CommentStatus::Approved->value)
                        ->whereNull('parent_id'),
                ),
            ],
        ]);
        $parent = isset($validated['parent_id'])
            ? Comment::query()->approved()->where('post_id', $post->getKey())->findOrFail($validated['parent_id'])
            : null;
        $comment = $post->comments()->create([
            'user_id' => $request->user()->getAuthIdentifier(),
            'parent_id' => $parent?->getKey(),
            'body' => $validated['body'],
            'status' => CommentStatus::Pending,
        ]);

        if ($parent?->user !== null && $parent->user_id !== $request->user()->getAuthIdentifier()) {
            $parent->user->notify(new CommentRepliedNotification(
                $request->user()->name,
                route('site.news.show', ['locale' => $locale, 'slug' => $translation->slug]).'#comment-'.$comment->getKey(),
            ));
        }

        return back()->with('status', __('Your comment was submitted for moderation.'));
    }

    public function update(Request $request, string $locale, Comment $comment): RedirectResponse
    {
        $comment = Comment::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->findOrFail($comment->getKey());
        Gate::authorize('update', $comment);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);
        $comment->update([
            'body' => $validated['body'],
            'status' => CommentStatus::Pending,
        ]);

        return back()->with('status', __('Your comment was updated and resubmitted for moderation.'));
    }

    public function destroy(Request $request, string $locale, Comment $comment): RedirectResponse
    {
        $comment = Comment::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->findOrFail($comment->getKey());
        Gate::authorize('delete', $comment);
        $comment->delete();

        return back()->with('status', __('Your comment has been deleted.'));
    }
}
