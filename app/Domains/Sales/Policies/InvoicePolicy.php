<?php

declare(strict_types=1);

namespace App\Domains\Sales\Policies;

use App\Domains\Sales\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->company_id === $invoice->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_invoices');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->company_id === $invoice->company_id && $user->hasPermissionTo('manage_invoices');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->company_id === $invoice->company_id && $user->hasPermissionTo('manage_invoices');
    }
}
