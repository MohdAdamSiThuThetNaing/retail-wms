<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class WmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $category,
        public string $title,
        public string $message,
        public ?string $url = null,
        public array $metadata = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    public function broadcastType(): string
    {
        return 'wms.notification';
    }

    private function payload(): array
    {
        return [
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'metadata' => $this->metadata,
            'created_at' => now()->toISOString(),
        ];
    }
}
