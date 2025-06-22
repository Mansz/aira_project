<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LiveStreamNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastType(): string
    {
        return 'live.notification';
    }

    /**
     * Get the title for the notification.
     */
    public function getTitle(): string
    {
        return match($this->type) {
            'stream.started' => 'Live Stream Dimulai',
            'stream.ended' => 'Live Stream Berakhir',
            'voucher.used' => 'Voucher Digunakan',
            'voucher.created' => 'Voucher Baru',
            'order.created' => 'Pesanan Baru',
            default => 'Notifikasi Live Stream',
        };
    }

    /**
     * Get the message for the notification.
     */
    public function getMessage(): string
    {
        return match($this->type) {
            'stream.started' => "Live stream \"{$this->data['title']}\" telah dimulai.",
            'stream.ended' => "Live stream \"{$this->data['title']}\" telah berakhir.",
            'voucher.used' => "Voucher {$this->data['code']} telah digunakan dalam pesanan #{$this->data['order_number']}.",
            'voucher.created' => "Voucher baru {$this->data['code']} telah dibuat untuk live stream.",
            'order.created' => "Pesanan baru #{$this->data['order_number']} dari live stream.",
            default => $this->data['message'] ?? 'Notifikasi baru dari live stream.',
        };
    }
}