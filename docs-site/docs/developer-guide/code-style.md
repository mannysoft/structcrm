---
title: Code style
sidebar_position: 15
description: Formatting, PHP conventions and the path-scoped rules every contributor should read.
---

# Code style

## Formatting

Laravel Pint with the default Laravel preset — there is no `pint.json`.

```bash
vendor/bin/pint --dirty          # format changed files before committing
vendor/bin/pint                  # whole codebase
```

CI runs tests, not Pint, so formatting is a review responsibility. Run it anyway: unrelated
diffs make review harder.

## PHP conventions

| Rule | Example |
| :--- | :--- |
| `declare(strict_types=1);` at the top of domain classes | `declare(strict_types=1);` |
| Explicit parameter and return types | `public function execute(BillingData $data, int $companyId): Billing` |
| Constructor property promotion | `public function __construct(public readonly User $user) {}` |
| No empty zero-parameter constructors | Omit them entirely |
| `final` on domain classes and DTOs | `final class CreateLeadAction` |
| `final readonly` for DTOs and value objects | `final readonly class LeadData` |
| Curly braces on every control structure | `if ($x) { … }` |
| Enum cases in TitleCase | `case InProgress = 'in_progress';` |
| PHPDoc blocks over inline comments | Array shapes documented with `@param array{…}` |
| Descriptive names | `isRegisteredForDiscounts`, not `discount` |

## Naming

| Kind | Convention |
| :--- | :--- |
| Action | `CreateInvoiceAction`, `DecideChangeOrderAction` |
| DTO | `InvoiceData` |
| Request | `StoreInvoiceRequest`, `UpdateInvoiceRequest` |
| Resource | `InvoiceResource` |
| Policy | `InvoicePolicy` |
| Observer | `InvoiceObserver` |
| Livewire page | `resources/views/livewire/invoices/create.blade.php` |
| Test | `InvoicePageTest.php`, `it('creates an invoice')` |

## Comments

Only comment code that needs clarification. A comment explaining *why* (a non-obvious
constraint) earns its place; a comment restating *what* the next line does does not.

```php
// Change order amounts can be negative (credits), so keep raw cents instead of a
// Money hook, which rejects negatives.
```

## Blade conventions

- Reuse `resources/views/components/*` (`x-table`, `x-badge`, `x-page-header`, …).
- Use `text-brand-600` / `bg-accent-500` tokens, not raw hex.
- Keep logic out of views; put it in `with()` or the component class.
- One full-page component per file, with `new #[Layout('layouts.app')] class extends Component`.

## The committed rules

`.ai/rules` contains path-scoped conventions that encode non-obvious traps. Read
`.ai/rules/index.md` first; the highest-value ones are:

| Rule | Why it exists |
| :--- | :--- |
| `.ai/rules/domains.md` | `HasUuids` + integer PK requires a `uniqueIds()` override |
| `.ai/rules/models.md` | Money hook names must differ from their column; no hooks on negative columns |
| `.ai/rules/requests.md` | Kebab-case resources use snake_case route params |
| `.ai/rules/routes.md` | Create routes must be gated and registered in the prerequisites map |
| `.ai/rules/tests.md` | Tests are Pest functions, never PHPUnit classes |
| `.ai/rules/livewire.md` | Single-file component conventions |
| `.ai/rules/views.md` | Blade component and styling conventions |

Record new decisions with the Boost `record-rule` tool so the next contributor inherits
them.

## Git and commits

- Keep commits focused; one concern per commit.
- Describe *why* in the body when the change is not self-evident.
- No secrets, no credentials, no `.env` changes committed.
- Format before committing.

See [Contributing](/docs/contributing) for the full workflow.
