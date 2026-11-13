<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Gate::authorize('create', Post::class);

        if (! auth()->user()->can('posts.update')) {
            $data['author_id'] = auth()->id();
        }

        if (! auth()->user()->can('posts.publish')) {
            $data['is_featured'] = false;
            $data['is_breaking'] = false;
        }

        $data['status'] = 'draft';

        return $data;
    }
}
