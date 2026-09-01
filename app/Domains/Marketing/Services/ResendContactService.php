<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ResendContactService
{
    /**
     * Upsert the user as a Resend contact, mirroring their marketing consent.
     */
    public function sync(User $user): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        [$firstName, $lastName] = $this->nameParts($user->name);

        $attributes = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'unsubscribed' => ! $user->marketing_opt_in,
        ];

        // if ($companyName = $user->company?->name) {
        //     $attributes['properties'] = [
        //         'company_name' => $companyName,
        //     ];
        // }

        if ($segmentId = config('services.resend.segment_id')) {
            $attributes['segments'] = [['id' => $segmentId]];
        }

        $client = \Resend::client((string) config('services.resend.key'));

        try {
            $client->contacts->create(['email' => $user->email, ...$attributes]);
        } catch (Throwable $e) {
            // The contact already exists (e.g. a job retry) — update it instead.
            $client->contacts->update($user->email, $attributes);
        }
    }

    /**
     * Whether the Resend SDK and an API key are available.
     */
    private function isConfigured(): bool
    {
        if (! class_exists(\Resend::class)) {
            Log::warning('The resend/resend-php package is not installed; skipping Resend contact sync.');

            return false;
        }

        return (bool) config('services.resend.key');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function nameParts(string $name): array
    {
        $parts = explode(' ', trim($name), 2);

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }
}
