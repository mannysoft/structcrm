<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class StaffInvitation extends Notification implements ShouldQueue
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
            'staff.set-password',
            now()->addDays(7),
            ['user' => $notifiable->getKey()],
        );

        return (new MailMessage)
            ->subject('You have been invited to ConstructFlow')
            ->greeting('Hello!')
            ->line('You have been invited to join your team on ConstructFlow.')
            ->action('Set your password', $url)
            ->line('This secure link will expire in 7 days.');
    }
}
