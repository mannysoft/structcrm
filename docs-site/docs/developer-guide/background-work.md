---
title: Background work
sidebar_position: 11
description: Queued notifications and mail, observers, events and the scheduler.
---

# Background work

## Queued work

| Class | Queue | Trigger |
| :--- | :--- | :--- |
| `CompanyManagement\Notifications\StaffInvitation` | Yes (`ShouldQueue`) | Staff invitation |
| `Sales\Notifications\ClientInvitation` | Yes (`ShouldQueue`) | Client portal invitation |
| `Marketing\Mail\MarketingEmail` | Sent directly by the command | Daily campaign run |
| `Marketing\Jobs\SyncResendContactJob` | Yes | `Registered` event with marketing consent |

With `QUEUE_CONNECTION=database` (the default) nothing is sent until a worker runs:

```bash
php artisan queue:work            # long-running worker
php artisan queue:listen          # dev, reloads code per job
php artisan queue:failed          # inspect failures
php artisan queue:retry all
```

In tests `QUEUE_CONNECTION=sync` is configured in `phpunit.xml`, so jobs run inline.

## Observers

Document numbering is the only observer responsibility today:

```php
final class BillingObserver
{
    public function creating(Billing $billing): void
    {
        if (empty($billing->billing_number)) {
            $billing->billing_number = $this->nextNumber($billing->company_id);
        }
    }
}
```

They are attached with the `#[ObservedBy([...])]` attribute on the model — no registration
needed.

## Events and listeners

`Marketing\Listeners\SyncResendContact` listens to Laravel's `Registered` event:

```php
public function handle(Registered $event): void
{
    if (! $event->user->marketing_opt_in) {
        return;
    }

    SyncResendContactJob::dispatch($event->user->id);
}
```

Laravel 13 discovers listeners automatically; there is no `EventServiceProvider` list to
maintain.

## Scheduler

`routes/console.php` defines one scheduled command:

```php
Schedule::command(SendDueMarketingEmails::class)
    ->dailyAt('09:00')
    ->withoutOverlapping();
```

Run it locally with `php artisan schedule:work`. In production add the standard cron entry:

```cron
* * * * * cd /path/to/structcrm && php artisan schedule:run >> /dev/null 2>&1
```

Without the cron entry (or a platform scheduler), marketing campaigns never send.

## Commands

| Command | Purpose |
| :--- | :--- |
| `marketing:send-due-emails` | Send every due campaign step to opted-in users |
| `inspire` | Laravel's sample command |

## Long-running work

There is no Horizon and no Redis requirement. If you introduce heavy work (PDF rendering,
image processing), keep it queued, give it a timeout, and add `failed_jobs` monitoring — the
`failed_jobs` table already exists.
