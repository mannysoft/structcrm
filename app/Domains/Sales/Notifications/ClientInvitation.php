<?php

declare(strict_types=1);

namespace App\Domains\Sales\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ClientInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'portal.set-password',
            now()->addDays(7),
            ['clientUser' => $notifiable->uuid],
        );

        return (new MailMessage)
            ->subject('Set up your client portal password')
            ->greeting('Hello!')
            ->line('You have been invited to access your client portal.')
            ->action('Set your password', $url)
            ->line('This secure link will expire in 7 days.');
    }
}
