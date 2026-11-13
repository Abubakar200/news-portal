<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Comments\Pages\ListComments;
use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Category;
use App\Models\Comment;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseFourAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_moderators_can_approve_pending_comments_and_notify_the_author(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $author = User::factory()->create();
        $post = $this->publishedPost('moderated-story');
        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Needs review.',
            'status' => CommentStatus::Pending,
        ]);
        $this->actingAs($editor);

        Livewire::test(ListComments::class)
            ->assertCanSeeTableRecords([$comment])
            ->callAction(TestAction::make('approve')->table($comment))
            ->assertHasNoActionErrors();

        $this->assertSame(CommentStatus::Approved, $comment->fresh()->status);
        $notification = $author->notifications()->firstOrFail();
        $this->assertSame('comment_status_approved', $notification->data['type']);
    }

    public function test_newsletter_management_is_limited_to_service_managers(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'admin@example.test',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        $this->actingAs($editor);

        $this->get('/admin/newsletter-subscribers')->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(ListNewsletterSubscribers::class)
            ->assertCanSeeTableRecords([$subscriber])
            ->callAction(TestAction::make('delete')->table($subscriber))
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted($subscriber);
    }

    public function test_admin_can_soft_delete_content_records_from_each_management_list(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $post = Post::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $admin->id,
            'body' => 'A comment for moderation.',
            'status' => CommentStatus::Pending,
        ]);
        $this->actingAs($admin);

        Livewire::test(ListPosts::class)
            ->callAction(TestAction::make('delete')->table($post))
            ->assertHasNoActionErrors();
        Livewire::test(ListCategories::class)
            ->callAction(TestAction::make('delete')->table($category))
            ->assertHasNoActionErrors();
        Livewire::test(ListTags::class)
            ->callAction(TestAction::make('delete')->table($tag))
            ->assertHasNoActionErrors();
        Livewire::test(ListComments::class)
            ->callAction(TestAction::make('delete')->table($comment))
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted($post);
        $this->assertSoftDeleted($category);
        $this->assertSoftDeleted($tag);
        $this->assertSoftDeleted($comment);
    }

    public function test_registered_user_list_includes_regular_users_and_can_restore_them(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $regularUser = User::factory()->create();
        $author = User::factory()->create();
        $author->assignRole('author');
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor)
            ->get('/admin/users')
            ->assertForbidden();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$regularUser, $author])
            ->assertSee('System roles')
            ->assertSee('Registered user')
            ->assertSee('Author')
            ->assertSee('Admin')
            ->callAction(TestAction::make('delete')->table($regularUser))
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted($regularUser);

        Livewire::test(ListUsers::class)
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$regularUser])
            ->callAction(TestAction::make('restore')->table($regularUser))
            ->assertHasNoActionErrors();

        $this->assertNotSoftDeleted($regularUser);
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
}
