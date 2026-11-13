<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_save_and_remove_only_their_own_bookmarks(): void
    {
        $post = $this->publishedPost('saved-story');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->post(route('bookmarks.store', ['locale' => 'en', 'post' => $post]))
            ->assertRedirect();
        $this->post(route('bookmarks.store', ['locale' => 'en', 'post' => $post]))
            ->assertRedirect();

        $this->assertDatabaseCount('bookmarks', 1);
        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($owner)
            ->delete(route('bookmarks.destroy', ['locale' => 'en', 'post' => $post]))
            ->assertRedirect();

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $otherUser->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($otherUser)
            ->delete(route('bookmarks.destroy', ['locale' => 'en', 'post' => $post]))
            ->assertRedirect();

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_guests_are_redirected_to_sign_in_before_bookmarking(): void
    {
        $post = $this->publishedPost('guest-bookmark');

        $this->post(route('bookmarks.store', ['locale' => 'en', 'post' => $post]))
            ->assertRedirect(route('login', ['locale' => 'en']));
    }

    public function test_users_cannot_bookmark_unpublished_articles(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('bookmarks.store', ['locale' => 'en', 'post' => $post]))
            ->assertNotFound();

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_comments_are_moderated_and_rendered_as_escaped_text(): void
    {
        $post = $this->publishedPost('comment-story');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('comments.store', ['locale' => 'en', 'slug' => 'comment-story']), [
                'body' => '<script>alert("xss")</script>',
            ])
            ->assertRedirect();

        $comment = Comment::query()->firstOrFail();
        $this->assertSame(CommentStatus::Pending, $comment->status);

        $this->get(route('site.news.show', ['locale' => 'en', 'slug' => 'comment-story']))
            ->assertOk()
            ->assertDontSee('<script>alert("xss")</script>', false);

        $comment->update(['status' => CommentStatus::Approved]);

        $this->get(route('site.news.show', ['locale' => 'en', 'slug' => 'comment-story']))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_replies_notify_the_parent_author_and_remain_moderated(): void
    {
        $post = $this->publishedPost('reply-story');
        $originalAuthor = User::factory()->create();
        $replyAuthor = User::factory()->create(['name' => 'Reply Reader']);
        $parent = $this->createComment($post, $originalAuthor, 'Original comment.', CommentStatus::Approved);

        $this->actingAs($replyAuthor)
            ->post(route('comments.store', ['locale' => 'en', 'slug' => 'reply-story']), [
                'parent_id' => $parent->id,
                'body' => 'A helpful reply.',
            ])
            ->assertRedirect();

        $notification = $originalAuthor->notifications()->firstOrFail();
        $this->assertSame('comment_reply', $notification->data['type']);
        $this->assertSame('Reply Reader', $notification->data['author_name']);
        $this->assertStringContainsString('#comment-', $notification->data['url']);
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'body' => 'A helpful reply.',
            'status' => CommentStatus::Pending->value,
        ]);
    }

    public function test_a_comment_cannot_be_replied_to_from_another_article(): void
    {
        $post = $this->publishedPost('first-story');
        $otherPost = $this->publishedPost('second-story');
        $author = User::factory()->create();
        $replyAuthor = User::factory()->create();
        $otherPostComment = $this->createComment($otherPost, $author, 'Unrelated comment.', CommentStatus::Approved);

        $this->actingAs($replyAuthor)
            ->post(route('comments.store', ['locale' => 'en', 'slug' => 'first-story']), [
                'parent_id' => $otherPostComment->id,
                'body' => 'Cross-article reply.',
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('comments', [
            'post_id' => $post->id,
            'body' => 'Cross-article reply.',
        ]);
    }

    public function test_users_cannot_delete_another_users_comment(): void
    {
        $post = $this->publishedPost('owned-comment-story');
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = $this->createComment($post, $author, 'Keep this comment.');

        $this->actingAs($otherUser)
            ->delete(route('comments.destroy', ['locale' => 'en', 'comment' => $comment]))
            ->assertNotFound();

        $this->assertModelExists($comment);
    }

    public function test_users_cannot_edit_another_users_comment(): void
    {
        $post = $this->publishedPost('private-comment-story');
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $comment = $this->createComment($post, $author, 'Do not edit this.');

        $this->actingAs($otherUser)
            ->patch(route('comments.update', ['locale' => 'en', 'comment' => $comment]), [
                'body' => 'Unauthorized edit.',
            ])
            ->assertNotFound();

        $this->assertSame('Do not edit this.', $comment->fresh()->body);
    }

    public function test_comment_edits_return_to_moderation(): void
    {
        $post = $this->publishedPost('edit-comment-story');
        $user = User::factory()->create();
        $comment = $this->createComment($post, $user, 'Original text.', CommentStatus::Approved);

        $this->actingAs($user)
            ->patch(route('comments.update', ['locale' => 'en', 'comment' => $comment]), [
                'body' => 'Updated text.',
            ])
            ->assertRedirect();

        $this->assertSame('Updated text.', $comment->fresh()->body);
        $this->assertSame(CommentStatus::Pending, $comment->fresh()->status);
    }

    public function test_newsletter_subscriptions_are_normalized_and_unique(): void
    {
        $this->post(route('newsletter.store', ['locale' => 'en']), [
            'email' => ' Reader@Example.test ',
        ])->assertRedirect();
        $this->post(route('newsletter.store', ['locale' => 'en']), [
            'email' => 'reader@example.test',
        ])->assertRedirect();

        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'reader@example.test',
            'is_active' => true,
        ]);
    }

    public function test_deactivated_newsletter_subscribers_can_subscribe_again(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.test',
            'is_active' => false,
            'subscribed_at' => now()->subDay(),
        ]);

        $this->post(route('newsletter.store', ['locale' => 'en']), [
            'email' => $subscriber->email,
        ])->assertRedirect();

        $this->assertTrue($subscriber->fresh()->is_active);
        $this->assertTrue($subscriber->fresh()->subscribed_at->isToday());
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_soft_deleted_newsletter_subscribers_can_subscribe_again(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.test',
            'is_active' => true,
            'subscribed_at' => now()->subDay(),
        ]);
        $subscriber->delete();

        $this->post(route('newsletter.store', ['locale' => 'en']), [
            'email' => $subscriber->email,
        ])->assertRedirect();

        $this->assertNotSoftDeleted($subscriber->fresh());
        $this->assertTrue($subscriber->fresh()->is_active);
        $this->assertTrue($subscriber->fresh()->subscribed_at->isToday());
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_comment_submission_is_rate_limited_per_authenticated_user(): void
    {
        $post = $this->publishedPost('rate-limited-story');
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('comments.store', ['locale' => 'en', 'slug' => 'rate-limited-story']), [
                'body' => 'Comment '.$attempt,
            ])->assertRedirect();
        }

        $this->post(route('comments.store', ['locale' => 'en', 'slug' => 'rate-limited-story']), [
            'body' => 'Excessive comment.',
        ])->assertTooManyRequests();

        $this->assertDatabaseCount('comments', 5);
    }

    public function test_notification_pages_render_updates_and_mark_them_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new CommentStatusChangedNotification(CommentStatus::Approved));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('notifications.index', ['locale' => 'ur']))
            ->assertOk()
            ->assertSee('آپ کا تبصرہ منظور ہو گیا ہے۔');

        $this->patch(route('notifications.read', [
            'locale' => 'en',
            'notification' => $notification->id,
        ]))->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function publishedPost(string $slug): Post
    {
        $post = Post::factory()->create([
            'status' => PostStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
        $post->translations()->create([
            'locale' => 'en',
            'title' => 'Headline for '.$slug,
            'slug' => $slug,
            'body' => 'Article body.',
        ]);

        return $post;
    }

    private function createComment(
        Post $post,
        User $user,
        string $body,
        CommentStatus $status = CommentStatus::Pending,
    ): Comment {
        return Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => $body,
            'status' => $status,
        ]);
    }
}
