<?php

namespace App\View\Components;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NewsCard extends Component
{
    public function __construct(
        public Post $post,
        public string $locale,
        public bool $featured = false,
    ) {}

    public function render(): View
    {
        return view('components.news-card');
    }
}
