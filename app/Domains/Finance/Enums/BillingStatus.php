<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum BillingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Certified = 'certified';
    case Due = 'due';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';

    /**
     * Whether the status implies the billing has been certified.
     */
    public function certifies(): bool
    {
        return in_array($this, [
            self::Certified,
            self::Due,
            self::PartiallyPaid,
            self::Paid,
        ], true);
    }
}
