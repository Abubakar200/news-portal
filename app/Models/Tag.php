<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name_en', 'name_ur', 'slug'])]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, SoftDeletes;

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }
}
