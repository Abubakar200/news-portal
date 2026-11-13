<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicNewsController extends Controller
{
    public function home(string $locale): View
    {
        $latestPosts = $this->latestPosts($locale);
        $featuredPost = (clone $latestPosts)->where('is_featured', true)->first()
            ?? (clone $latestPosts)->first();
        $breakingPosts = (clone $latestPosts)->where('is_breaking', true)->limit(6)->get();
        $popularPosts = $this->publishedPosts($locale)
            ->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
        $categoryNews = Category::query()
            ->where('is_active', true)
            ->whereHas('posts', fn (Builder $query): Builder => $query->publishedForLocale($locale))
            ->with(['posts' => function (HasMany $query) use ($locale): void {
                $query->publishedForLocale($locale)
                    ->with([
                        'translations' => function (HasMany $translations) use ($locale): void {
                            $translations->where('locale', $locale);
                        },
                        'author',
                        'category',
                    ])
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(4);
            }])
            ->orderBy('name_en')
            ->limit(6)
            ->get();

        return view('home', [
            'locale' => $locale,
            'localeSwitchUrl' => route('site.home', ['locale' => $this->otherLocale($locale)]),
            'featuredPost' => $featuredPost,
            'breakingPosts' => $breakingPosts,
            'latestPosts' => (clone $latestPosts)->limit(8)->get(),
            'popularPosts' => $popularPosts,
            'categoryNews' => $categoryNews,
            'seoTitle' => __('Latest News'),
            'seoDescription' => __('Stay up to date with the latest stories and headlines.'),
            'canonicalUrl' => route('site.home', ['locale' => $locale]),
        ]);
    }

    public function index(Request $request, string $locale): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['q'] ?? '');
        $posts = $this->latestPosts($locale);

        if ($search !== '') {
            $posts->whereHas('translations', function (Builder $query) use ($locale, $search): void {
                $query->where('locale', $locale)
                    ->where(function (Builder $query) use ($search): void {
                        $query->where('title', 'like', '%'.$search.'%')
                            ->orWhere('excerpt', 'like', '%'.$search.'%')
                            ->orWhere('body', 'like', '%'.$search.'%');
                    });
            });
        }

        return view('news.index', [
            'locale' => $locale,
            'localeSwitchUrl' => route('site.news.index', [
                'locale' => $this->otherLocale($locale),
                ...($search !== '' ? ['q' => $search] : []),
            ]),
            'title' => $search !== '' ? __('Search results') : __('Latest News'),
            'description' => $search !== ''
                ? __('Results for :query', ['query' => $search])
                : __('The latest stories from our newsroom.'),
            'posts' => $posts->paginate(12)->withQueryString(),
            'seoTitle' => $search !== '' ? __('Search results') : __('Latest News'),
            'seoDescription' => __('The latest stories from our newsroom.'),
            'canonicalUrl' => route('site.news.index', ['locale' => $locale]),
            'search' => $search,
        ]);
    }

    public function show(Request $request, string $locale, string $slug): View
    {
        $translation = PostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('post', fn (Builder $query): Builder => $query->publishedForLocale($locale))
            ->with([
                'post.author',
                'post.category',
                'post.tags',
                'post.translations',
            ])
            ->firstOrFail();
        $post = $translation->post;
        $post->increment('view_count');
        $comments = $post->comments()
            ->approved()
            ->whereNull('parent_id')
            ->with([
                'user:id,name',
                'replies' => function (HasMany $replies): void {
                    $replies->approved()->with('user:id,name')->oldest();
                },
            ])
            ->oldest()
            ->paginate(20);
        $isBookmarked = $request->user()?->bookmarkedPosts()
            ->whereKey($post->getKey())
            ->exists() ?? false;
        $relatedPosts = $this->relatedPosts($post, $locale);
        $alternateTranslation = $post->translations->first(
            fn (PostTranslation $candidate): bool => $candidate->locale === $this->otherLocale($locale),
        );
        $canonicalUrl = $translation->canonical_url
            ?: route('site.news.show', ['locale' => $locale, 'slug' => $translation->slug]);
        $imageUrl = $post->featured_image
            ? url(Storage::disk('public')->url($post->featured_image))
            : null;
        $author = $post->author;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $translation->title,
            'description' => $translation->meta_description ?: $translation->excerpt,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'inLanguage' => $locale,
            'mainEntityOfPage' => $canonicalUrl,
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name') === 'Laravel' ? __('News Portal') : config('app.name'),
            ],
        ];

        if ($imageUrl !== null) {
            $schema['image'] = [$imageUrl];
        }

        if ($author !== null) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $author->name,
            ];
        }

        if ($post->category !== null) {
            $schema['articleSection'] = $locale === 'ur'
                ? $post->category->name_ur
                : $post->category->name_en;
        }

        return view('news.show', [
            'locale' => $locale,
            'localeSwitchUrl' => $alternateTranslation
                ? route('site.news.show', [
                    'locale' => $alternateTranslation->locale,
                    'slug' => $alternateTranslation->slug,
                ])
                : route('site.news.index', ['locale' => $this->otherLocale($locale)]),
            'translation' => $translation,
            'post' => $post,
            'comments' => $comments,
            'isBookmarked' => $isBookmarked,
            'relatedPosts' => $relatedPosts,
            'canonicalUrl' => $canonicalUrl,
            'seoTitle' => $translation->meta_title ?: $translation->title,
            'seoDescription' => $translation->meta_description ?: $translation->excerpt,
            'seoImage' => $imageUrl,
            'seoType' => 'article',
            'schema' => $schema,
            'articleBody' => Str::markdown($translation->body, [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }

    public function category(string $locale, Category $category): View
    {
        abort_unless($category->is_active, 404);

        $posts = $category->posts()
            ->publishedForLocale($locale)
            ->with($this->postRelations($locale))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
        $name = $locale === 'ur' ? $category->name_ur : $category->name_en;

        return view('news.index', [
            'locale' => $locale,
            'localeSwitchUrl' => route('site.categories.show', [
                'locale' => $this->otherLocale($locale),
                'category' => $category->slug,
            ]),
            'title' => $name,
            'description' => __('Latest stories in :category', ['category' => $name]),
            'posts' => $posts,
            'seoTitle' => $name,
            'seoDescription' => __('Latest stories in :category', ['category' => $name]),
            'canonicalUrl' => route('site.categories.show', ['locale' => $locale, 'category' => $category->slug]),
            'search' => '',
        ]);
    }

    public function tag(string $locale, Tag $tag): View
    {
        $posts = $tag->posts()
            ->publishedForLocale($locale)
            ->with($this->postRelations($locale))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
        $name = $locale === 'ur' ? $tag->name_ur : $tag->name_en;

        abort_if($posts->total() === 0, 404);

        return view('news.index', [
            'locale' => $locale,
            'localeSwitchUrl' => route('site.tags.show', [
                'locale' => $this->otherLocale($locale),
                'tag' => $tag->slug,
            ]),
            'title' => $name,
            'description' => __('Stories tagged :tag', ['tag' => $name]),
            'posts' => $posts,
            'seoTitle' => $name,
            'seoDescription' => __('Stories tagged :tag', ['tag' => $name]),
            'canonicalUrl' => route('site.tags.show', ['locale' => $locale, 'tag' => $tag->slug]),
            'search' => '',
        ]);
    }

    public function author(string $locale, User $author, string $slug): View|RedirectResponse
    {
        $authorSlug = Str::slug($author->name) ?: 'author-'.$author->getKey();

        if ($slug !== $authorSlug) {
            return redirect()->route('site.authors.show', [
                'locale' => $locale,
                'author' => $author,
                'slug' => $authorSlug,
            ], 301);
        }

        $posts = $author->posts()
            ->publishedForLocale($locale)
            ->with($this->postRelations($locale))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        abort_if($posts->total() === 0, 404);

        return view('authors.show', [
            'locale' => $locale,
            'localeSwitchUrl' => route('site.authors.show', [
                'locale' => $this->otherLocale($locale),
                'author' => $author,
                'slug' => $authorSlug,
            ]),
            'author' => $author,
            'posts' => $posts,
            'seoTitle' => __('Articles by :author', ['author' => $author->name]),
            'seoDescription' => __('Read the latest articles by :author.', ['author' => $author->name]),
            'canonicalUrl' => route('site.authors.show', [
                'locale' => $locale,
                'author' => $author,
                'slug' => $authorSlug,
            ]),
        ]);
    }

    /**
     * @return Builder<Post>
     */
    private function publishedPosts(string $locale): Builder
    {
        return Post::query()
            ->publishedForLocale($locale)
            ->with($this->postRelations($locale));
    }

    /**
     * @return Builder<Post>
     */
    private function latestPosts(string $locale): Builder
    {
        return $this->publishedPosts($locale)
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * @return array{translations: \Closure, author: string, category: string}
     */
    private function postRelations(string $locale): array
    {
        return [
            'translations' => function (HasMany $translations) use ($locale): void {
                $translations->where('locale', $locale);
            },
            'author',
            'category',
        ];
    }

    /**
     * @return Collection<int, Post>
     */
    private function relatedPosts(Post $post, string $locale): Collection
    {
        $tagIds = $post->tags->modelKeys();

        if ($post->category_id === null && $tagIds === []) {
            return collect();
        }

        return $this->publishedPosts($locale)
            ->where('posts.id', '<>', $post->id)
            ->where(function (Builder $query) use ($post, $tagIds): void {
                if ($post->category_id !== null) {
                    $query->where('category_id', $post->category_id);
                }

                if ($tagIds !== []) {
                    if ($post->category_id !== null) {
                        $query->orWhereHas('tags', fn (Builder $tags): Builder => $tags->whereIn('tags.id', $tagIds));
                    } else {
                        $query->whereHas('tags', fn (Builder $tags): Builder => $tags->whereIn('tags.id', $tagIds));
                    }
                }
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get();
    }

    private function otherLocale(string $locale): string
    {
        return $locale === 'ur' ? 'en' : 'ur';
    }
}
