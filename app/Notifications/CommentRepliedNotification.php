<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CommentRepliedNotification extends Notification
{
    public function __construct(
        public readonly string $authorName,
        public readonly string $url,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment_reply',
            'author_name' => $this->authorName,
            'url' => $this->url,
        ];
    }
}
