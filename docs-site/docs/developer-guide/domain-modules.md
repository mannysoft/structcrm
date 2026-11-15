---
title: Domain modules
sidebar_position: 3
description: The anatomy of a StructCRM domain module and how the pieces collaborate.
---

# Domain modules

Each business area is a self-contained folder under `app/Domains`, holding everything it
needs except shared helpers and the view layer.

```text
app/Domains/Sales/
├── Actions/          # CreateInvoiceAction, DecideChangeOrderAction, MarkInvoicePaidAction…
├── Controllers/      # API controllers (invoice, client, estimate, …)
├── DTOs/            # Immutable argument objects
├── Enums/           # InvoiceStatus, EstimateStatus, ChangeOrderStatus…
├── Models/          # Invoice, Estimate, ChangeOrder, Client, Lead, Supplier, Expense…
├── Notifications/   # ClientInvitation
├── Observers/       # InvoiceObserver, ChangeOrderObserver (document numbering)
├── Policies/        # InvoicePolicy, ClientPolicy…
├── Repositories/    # ClientRepository (Sales only)
├── Requests/        # Store/Update form requests
├── Resources/       # API resources
└── Services/        # ClientService (Sales only)
```

## Domain map

| Domain | Owns |
| :--- | :--- |
| **CompanyManagement** | `Company`, staff users and roles, invitations, the getting-started checklist |
| **ProjectManagement** | `Project`, `Task`, `DailyLog`, `ProjectPhoto`, `Document`, `TimeEntry`, `Equipment`, `MaintenanceLog` |
| **Finance** | `Contract`, `CostCode`, `BoqItem`, `Budget`, `BudgetItem`, `ProgressReport`, `ProgressItem`, `Billing`, `BillingItem`, `BillingPayment`, BOQ import |
| **Sales** | `Client`, `Lead`, `Supplier`, `Estimate`, `Invoice`, `ChangeOrder`, `PurchaseOrder`, `Expense` and their line items |
| **Marketing** | `MarketingCampaign`, `MarketingCampaignStep`, `MarketingEmailLog`, Resend sync |

## The write path, piece by piece

Taking `CreateInvoiceAction` as the example:

1. **Request** — `StoreInvoiceRequest` authorises (`can('create', Invoice::class)`), validates
   and maps to `InvoiceData`.
2. **DTO** — `InvoiceData` is `final readonly`; the action cannot mutate its input.
3. **Action** — wraps everything in `DB::transaction`: computes `total` from the lines,
   creates the invoice, then creates each `InvoiceItem`.
4. **Observer** — `InvoiceObserver::creating` sees an empty `invoice_number` and assigns
   `INV-<n>`.
5. **View** — the Livewire component redirects and flashes a status message.

```php
final class CreateInvoiceAction
{
    public function execute(InvoiceData $data, int $companyId): Invoice
    {
        return DB::transaction(function () use ($data, $companyId) {
            $total = collect($data->items)->sum(
                fn (array $item) => $item['quantity'] * $item['unit_price'],
            );

            $invoice = Invoice::create([...]);

            foreach ($data->items as $item) {
                $invoice->items()->create([...]);
            }

            return $invoice->load('items');
        });
    }
}
```

## Actions are the only write path

| Caller | Entry point | Ends in |
| :--- | :--- | :--- |
| Web UI | Livewire component | Action |
| REST API | Controller + FormRequest | Action |
| AI assistant | Tool class | Action |
| Tests | Directly | Action |

If you add business logic anywhere other than an action, you will have to duplicate it three
times.

## Document numbering

Numbers are assigned by observers so that every write path gets one:

| Model | Observer | Format |
| :--- | :--- | :--- |
| Project | `ProjectObserver` | `PRJ-<year>-<000>` |
| Contract | `ContractObserver` | `CON-<n>` |
| Progress report | `ProgressReportObserver` | `REP-<n>` |
| Billing | `BillingObserver` | `BIL-<n>` |
| Estimate | `EstimateObserver` | `EST-<n>` |
| Invoice | `InvoiceObserver` | `INV-<n>` |
| Change order | `ChangeOrderObserver` | `CO-<n>` |
| Purchase order | `PurchaseOrderObserver` | `PO-<n>` |

Sequences are `count(withTrashed()) + 1` per company — simple, readable, and **not**
concurrency-safe. Two simultaneous inserts in the same company can collide. If you need
strict uniqueness, move to a database sequence or a locked counter.

## API resources

Resources expose the **UUID** as `id`, never the integer key, and format dates as
ISO-8601:

```php
return [
    'id' => $this->uuid,
    'first_name' => $this->first_name,
    'status' => $this->status->value,
    'created_at' => $this->created_at?->toIso8601String(),
];
```

## Clients are the one exception

`Sales` uses a `ClientRepository` + `ClientService` pair instead of a single action. It is a
legacy pattern; new work should use actions so behaviour stays consistent.
