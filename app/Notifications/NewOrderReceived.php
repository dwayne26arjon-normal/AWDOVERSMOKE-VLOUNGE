<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderReceived extends Notification
{
    use Queueable;

    public function __construct(private Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'buyer' => $this->order->customer_name,
            'total' => (float) $this->order->total,
            'items' => (int) $this->order->items_count,
            'message' => 'New purchase from ' . $this->order->customer_name . '.',
        ];
    }
}
