<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Jobs;

use App\Domains\Marketing\Services\ResendContactService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncResendContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 120];

    public function __construct(public int $userId) {}

    /**
     * Sync the user to the Resend audience.
     */
    public function handle(ResendContactService $service): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $service->sync($user);
    }
}
