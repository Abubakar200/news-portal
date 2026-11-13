<?php

namespace App\Models;

use Database\Factories\PostTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'post_id',
    'locale',
    'title',
    'slug',
    'excerpt',
    'body',
    'meta_title',
    'meta_description',
    'canonical_url',
    'image_alt',
])]
class PostTranslation extends Model
{
    /** @use HasFactory<PostTranslationFactory> */
    use HasFactory;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
