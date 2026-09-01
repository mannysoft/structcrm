<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Listeners;

use App\Domains\Marketing\Jobs\SyncResendContactJob;
use Illuminate\Auth\Events\Registered;

class SyncResendContact
{
    /**
     * Queue opted-in signups to be stored as Resend contacts.
     */
    public function handle(Registered $event): void
    {
        if (! $event->user->marketing_opt_in) {
            return;
        }

        SyncResendContactJob::dispatch($event->user->id);
    }
}
