<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    public function __construct(private Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable->order_notifications_enabled ? ['database'] : [];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'message' => 'We received your order and are processing it.',
        ];
    }
}
