<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'author_id',
    'category_id',
    'featured_image',
    'status',
    'scheduled_at',
    'published_at',
    'is_featured',
    'is_breaking',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => PostStatus::Draft->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    #[Scope]
    protected function publishedForLocale(Builder $query, string $locale): Builder
    {
        return $query
            ->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('translations', fn (Builder $translations): Builder => $translations->where('locale', $locale));
    }

    public function publish(): void
    {
        $this->forceFill([
            'status' => PostStatus::Published,
            'published_at' => now(),
            'scheduled_at' => null,
        ])->save();
    }

    public function scheduleFor(\DateTimeInterface $scheduledAt): void
    {
        $this->forceFill([
            'status' => PostStatus::Scheduled,
            'scheduled_at' => $scheduledAt,
            'published_at' => null,
        ])->save();
    }

    public function saveAsDraft(): void
    {
        $this->forceFill([
            'status' => PostStatus::Draft,
            'scheduled_at' => null,
            'published_at' => null,
        ])->save();
    }
}
