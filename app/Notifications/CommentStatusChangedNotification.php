<?php

namespace App\Notifications;

use App\Enums\CommentStatus;
use Illuminate\Notifications\Notification;

class CommentStatusChangedNotification extends Notification
{
    public function __construct(
        public readonly CommentStatus $status,
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
            'type' => 'comment_status_'.$this->status->value,
        ];
    }
}
