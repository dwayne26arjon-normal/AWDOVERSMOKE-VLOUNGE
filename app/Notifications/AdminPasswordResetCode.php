<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPasswordResetCode extends Notification
{
    use Queueable;

    public function __construct(public readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Oversmoke Vlounge admin password reset code')
            ->greeting('Admin password reset requested')
            ->line('Enter this verification code to reset your store account password:')
            ->line($this->code)
            ->line('This code expires in 10 minutes and can only be used once.')
            ->line('If you did not request a reset, you can ignore this email.');
    }
}
