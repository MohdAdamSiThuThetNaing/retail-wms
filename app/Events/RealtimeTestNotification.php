<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RealtimeTestNotification implements ShouldBroadcast
{
    public function __construct(
        public int $userId,
        public string $message,
        public string $sentAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'realtime.notification';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'sent_at' => $this->sentAt,
        ];
    }
}
