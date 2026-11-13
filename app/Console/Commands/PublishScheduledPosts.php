<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:publish-scheduled')]
#[Description('Publish news articles whose scheduled time has arrived')]
class PublishScheduledPosts extends Command
{
    public function handle(): int
    {
        $publishedCount = Post::query()
            ->where('status', PostStatus::Scheduled->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->update([
                'status' => PostStatus::Published->value,
                'published_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Published {$publishedCount} scheduled article(s).");

        return self::SUCCESS;
    }
}
