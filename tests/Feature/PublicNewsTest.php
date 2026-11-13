<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_published_stories_in_the_requested_language(): void
    {
        $post = $this->publishedPost('en', 'community-update', [
            'is_featured' => true,
            'is_breaking' => true,
        ], [
            'title' => 'English community update',
        ]);
        $post->translations()->create([
            'locale' => 'ur',
            'title' => 'اردو کمیونٹی خبر',
            'slug' => 'urdu-community-update',
            'body' => 'اردو مضمون۔',
        ]);
        $this->publishedPost('en', 'most-read-story', [
            'view_count' => 25,
        ], [
            'title' => 'Most read story',
        ]);
        $draft = Post::factory()->create();
        $draft->translations()->create([
            'locale' => 'en',
            'title' => 'Unpublished draft headline',
            'slug' => 'unpublished-draft-headline',
            'body' => 'Draft body.',
        ]);

        $english = $this->get('/en');

        $english->assertOk()
            ->assertSee('English community update')
            ->assertSee('Most read story')
            ->assertDontSee('Unpublished draft headline')
            ->assertSee('dir="ltr"', false);

        $this->get('/ur')
            ->assertOk()
            ->assertSee('اردو کمیونٹی خبر')
            ->assertSee('dir="rtl"', false)
            ->assertDontSee('English community update');
    }

    public function test_article_page_increments_views_and_renders_safe_seo_content(): void
    {
        $post = $this->publishedPost('en', 'safe-headline', [
            'is_featured' => true,
        ], [
            'title' => 'Safe headline',
            'body' => "# Story\n\n<script>alert('xss')</script>",
            'meta_title' => 'SEO headline',
            'meta_description' => 'A search description.',
        ]);
        $this->publishedPost('en', 'related-headline', [
            'category_id' => $post->category_id,
        ], [
            'title' => 'Related headline',
        ]);

        $response = $this->get('/en/news/safe-headline');

        $response->assertOk()
            ->assertSee('SEO headline')
            ->assertSee('NewsArticle', false)
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false)
            ->assertSee('Related headline');

        $this->assertSame(1, $post->fresh()->view_count);
    }

    public function test_category_tag_and_author_pages_list_published_articles(): void
    {
        $post = $this->publishedPost('en', 'local-report', [], [
            'title' => 'Local report headline',
        ]);
        $tag = Tag::factory()->create([
            'name_en' => 'Local news',
            'slug' => 'local-news',
        ]);
        $post->tags()->attach($tag);
        $authorSlug = str()->slug($post->author->name);

        $this->get('/en/category/'.$post->category->slug)
            ->assertOk()
            ->assertSee('Local report headline');

        $this->get('/en/tag/'.$tag->slug)
            ->assertOk()
            ->assertSee('Local report headline');

        $this->get('/en/authors/'.$post->author->id.'/'.$authorSlug)
            ->assertOk()
            ->assertSee($post->author->name)
            ->assertSee('Local report headline');

        $this->get('/en/authors/'.$post->author->id.'/outdated-name')
            ->assertRedirect(route('site.authors.show', [
                'locale' => 'en',
                'author' => $post->author,
                'slug' => $authorSlug,
            ]))
            ->assertStatus(301);
    }

    public function test_news_listing_paginates_and_search_terms_are_literal(): void
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();

        for ($index = 0; $index < 13; $index++) {
            $post = Post::factory()
                ->for($author, 'author')
                ->for($category)
                ->create([
                    'status' => PostStatus::Published,
                    'published_at' => now()->subMinutes(13 - $index),
                ]);
            $post->translations()->create([
                'locale' => 'en',
                'title' => sprintf('List headline %02d', $index),
                'slug' => sprintf('list-headline-%02d', $index),
                'body' => 'A published story.',
            ]);
        }

        $this->get('/en/news')
            ->assertOk()
            ->assertSee('List headline 12')
            ->assertDontSee('List headline 00')
            ->assertSee('page=2', false);

        $this->get('/en/news?q=%27%20OR%201%3D1%20--')
            ->assertOk()
            ->assertSee('No stories found.');
    }

    public function test_search_results_escape_untrusted_headlines(): void
    {
        $this->publishedPost('en', 'unsafe-title', [], [
            'title' => "<script>alert('news')</script>",
        ]);

        $this->get('/en/news?q=script')
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('news')</script>", false);
    }

    public function test_search_rejects_queries_over_one_hundred_characters(): void
    {
        $this->get('/en/news?q='.str_repeat('x', 101))
            ->assertSessionHasErrors([
                'q' => 'The q field must not be greater than 100 characters.',
            ]);
    }

    public function test_article_slug_must_exist_in_the_requested_language(): void
    {
        $this->publishedPost('en', 'english-only-story', [], [
            'title' => 'English-only story',
        ]);

        $this->get('/ur/news/english-only-story')->assertNotFound();
    }

    private function publishedPost(
        string $locale,
        string $slug,
        array $postAttributes = [],
        array $translationAttributes = [],
    ): Post {
        $post = Post::factory()->create([
            ...$postAttributes,
            'status' => PostStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
        $post->translations()->create([
            ...$translationAttributes,
            'locale' => $locale,
            'title' => $translationAttributes['title'] ?? 'Headline '.$slug,
            'slug' => $slug,
            'body' => $translationAttributes['body'] ?? 'Article body.',
        ]);

        return $post;
    }
}
