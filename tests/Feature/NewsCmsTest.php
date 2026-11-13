<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Tags\Pages\CreateTag;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NewsCmsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_author_can_create_a_draft_with_language_versions_and_taxonomy(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $canonicalUrl = 'https://example.com/'.str_repeat('a', 280);
        $this->actingAs($author);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'category_id' => $category->id,
                'tags' => [$tag->id],
                'translations' => [
                    [
                        'locale' => 'en',
                        'title' => 'Community bulletin',
                        'slug' => 'community-bulletin',
                        'body' => 'The article body in English.',
                        'meta_title' => 'Community bulletin',
                        'meta_description' => 'Local community news.',
                        'canonical_url' => $canonicalUrl,
                    ],
                    [
                        'locale' => 'ur',
                        'title' => 'کمیونٹی خبریں',
                        'slug' => 'community-bulletin',
                        'body' => 'اردو میں مضمون۔',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::query()->where('author_id', $author->id)->firstOrFail();

        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertSame($category->id, $post->category_id);
        $this->assertDatabaseHas('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'en',
            'slug' => 'community-bulletin',
        ]);
        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'ur',
            'slug' => 'community-bulletin',
        ]);
        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'en',
            'canonical_url' => $canonicalUrl,
        ]);
    }

    public function test_author_cannot_assign_another_author_or_enable_editorial_flags(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $otherAuthor = User::factory()->create();
        $otherAuthor->assignRole('author');
        $this->actingAs($author);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'author_id' => $otherAuthor->id,
                'is_featured' => true,
                'is_breaking' => true,
                'translations' => [
                    [
                        'locale' => 'en',
                        'title' => 'Author-owned article',
                        'slug' => 'author-owned-article',
                        'body' => 'Draft body.',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::query()->where('author_id', $author->id)->firstOrFail();

        $this->assertSame($author->id, $post->author_id);
        $this->assertFalse($post->is_featured);
        $this->assertFalse($post->is_breaking);
    }

    public function test_article_slugs_must_be_unique_within_their_language(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $existingPost = Post::factory()->for($author, 'author')->create();
        PostTranslation::factory()->for($existingPost)->create([
            'locale' => 'en',
            'slug' => 'same-english-slug',
        ]);
        $this->actingAs($author);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'translations' => [
                    [
                        'locale' => 'en',
                        'title' => 'Duplicate slug',
                        'slug' => 'same-english-slug',
                        'body' => 'Article body.',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors();

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_author_can_edit_and_delete_only_their_own_articles(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $ownPost = Post::factory()->for($author, 'author')->create();
        $otherPost = Post::factory()->create();

        $this->assertTrue(Gate::forUser($author)->allows('update', $ownPost));
        $this->assertTrue(Gate::forUser($author)->allows('delete', $ownPost));
        $this->assertFalse(Gate::forUser($author)->allows('update', $otherPost));
        $this->assertFalse(Gate::forUser($author)->allows('delete', $otherPost));
        $this->assertFalse(Gate::forUser($author)->allows('publish', $ownPost));
        $this->assertFalse(Gate::forUser($author)->allows('create', Category::class));
        $this->assertFalse(Gate::forUser($author)->allows('create', Tag::class));
    }

    public function test_author_cannot_change_article_ownership_status_or_editorial_flags(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $otherAuthor = User::factory()->create();
        $otherAuthor->assignRole('author');
        $post = Post::factory()->for($author, 'author')->create();
        PostTranslation::factory()->for($post)->create();
        $this->actingAs($author);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->set('data.author_id', $otherAuthor->id)
            ->set('data.is_featured', true)
            ->set('data.is_breaking', true)
            ->set('data.status', PostStatus::Published->value)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($author->id, $post->fresh()->author_id);
        $this->assertSame(PostStatus::Draft, $post->fresh()->status);
        $this->assertFalse($post->fresh()->is_featured);
        $this->assertFalse($post->fresh()->is_breaking);
    }

    public function test_editors_can_publish_and_schedule_but_cannot_delete_articles(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $post = Post::factory()->create();

        $this->assertTrue(Gate::forUser($editor)->allows('update', $post));
        $this->assertTrue(Gate::forUser($editor)->allows('publish', $post));
        $this->assertTrue(Gate::forUser($editor)->allows('schedule', $post));
        $this->assertFalse(Gate::forUser($editor)->allows('delete', $post));
    }

    public function test_author_cannot_open_another_authors_edit_page(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $otherPost = Post::factory()->create();
        $this->actingAs($author);

        Livewire::test(EditPost::class, ['record' => $otherPost->getRouteKey()])
            ->assertNotFound();
    }

    public function test_author_article_list_is_scoped_to_their_own_records(): void
    {
        $author = User::factory()->create();
        $author->assignRole('author');
        $ownPost = Post::factory()->for($author, 'author')->create();
        $otherPost = Post::factory()->create();
        $this->actingAs($author);

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords([$ownPost])
            ->assertCanNotSeeTableRecords([$otherPost]);
    }

    public function test_editor_can_schedule_an_article_from_the_admin_action(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $author = User::factory()->create();
        $author->assignRole('author');
        $post = Post::factory()->for($author, 'author')->create();
        PostTranslation::factory()->for($post)->create();
        $scheduledAt = now()->addDay()->startOfMinute();
        $this->actingAs($editor);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->callAction(TestAction::make('schedule'), data: [
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(PostStatus::Scheduled, $post->fresh()->status);
        $this->assertSame($scheduledAt->timestamp, $post->fresh()->scheduled_at->timestamp);
    }

    public function test_scheduled_articles_publish_when_their_time_arrives(): void
    {
        $this->travelTo(now()->startOfMinute());
        $duePost = Post::factory()->create();
        $futurePost = Post::factory()->create();
        $duePost->scheduleFor(now()->subMinute());
        $futurePost->scheduleFor(now()->addMinute());

        $this->artisan('posts:publish-scheduled')
            ->expectsOutput('Published 1 scheduled article(s).')
            ->assertSuccessful();

        $this->assertSame(PostStatus::Published, $duePost->fresh()->status);
        $this->assertNotNull($duePost->fresh()->published_at);
        $this->assertSame(PostStatus::Scheduled, $futurePost->fresh()->status);
    }

    public function test_editor_can_publish_an_article_from_the_admin_action(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $author = User::factory()->create();
        $author->assignRole('author');
        $post = Post::factory()->for($author, 'author')->create();
        PostTranslation::factory()->for($post)->create();
        $this->actingAs($editor);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->callAction(TestAction::make('publish'))
            ->assertHasNoActionErrors();

        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_author_can_upload_a_featured_image(): void
    {
        Storage::fake('public');

        $author = User::factory()->create();
        $author->assignRole('author');
        $this->actingAs($author);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/r7kAAAAASUVORK5CYII=', true);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'featured_image' => UploadedFile::fake()->createWithContent('featured.png', $png),
                'translations' => [
                    [
                        'locale' => 'en',
                        'title' => 'Image article',
                        'slug' => 'image-article',
                        'body' => 'Article body.',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::query()->where('author_id', $author->id)->firstOrFail();

        $this->assertNotNull($post->featured_image);
        Storage::disk('public')->assertExists($post->featured_image);
    }

    public function test_admin_can_create_an_author_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'News Writer',
                'email' => 'writer@example.test',
                'password' => 'a-strong-password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $author = User::query()->where('email', 'writer@example.test')->firstOrFail();

        $this->assertTrue($author->hasRole('author'));
        $this->assertTrue($author->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name_en' => 'Pakistan News',
                'name_ur' => 'پاکستانی خبریں',
                'slug' => 'pakistan-news',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name_en' => 'Pakistan News',
            'name_ur' => 'پاکستانی خبریں',
            'slug' => 'pakistan-news',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_a_tag(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(CreateTag::class)
            ->fillForm([
                'name_en' => 'Public health',
                'name_ur' => 'صحت عامہ',
                'slug' => 'public-health',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tags', [
            'name_en' => 'Public health',
            'name_ur' => 'صحت عامہ',
            'slug' => 'public-health',
        ]);
    }
}
