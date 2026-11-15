---
title: Money and currency
sidebar_position: 5
description: Integer minor units, the Money value object, and the property hooks that keep them type-safe.
---

# Money and currency

## The rules

1. **Store money as integers in minor units** (`bigInteger` columns named `*_amount`,
   `unit_price`, `total`, `amount`).
2. **Never store floats.**
3. **Never let `Money` hold a negative amount** — it throws.
4. **Always pair an amount with its currency.**

## The value object

```php
final readonly class Money
{
    public function __construct(
        public int $amountInCents,
        public string $currency = 'USD',
    ) {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative.');
        }
    }

    public static function fromDecimal(float $amount, string $currency = 'USD'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function toDecimal(): float
    {
        return $this->amountInCents / 100;
    }
}
```

## Property hooks (PHP 8.4)

Models expose typed `Money` properties that read and write the underlying integer columns:

```php
public Money $unitPrice {
    get => new Money($this->unit_price, $this->currency);
    set(Money $money) {
        $this->unit_price = $money->amountInCents;
        $this->currency = $money->currency;
    }
}
```

:::danger The two hook rules
1. **The hook name must differ from the column.** A hook named `unitPrice` reading
   `$this->unitPrice` recurses forever. Read `unit_price`.
2. **Never attach a hook to a column that may hold a negative number.** `Money` rejects
   negatives, so `change_orders.amount` (which can be a credit) is kept as raw integer
   cents plus a plain `amount_currency` column.
:::

These rules are recorded in `.ai/rules/models.md` — read it before touching a model.

## Converting at the boundary

| Boundary | Direction | Code |
| :--- | :--- | :--- |
| Form input → DTO | decimal → cents | `(int) round((float) $value * 100)` |
| DTO → action | cents | `unit_price` is already an integer |
| Action → model | cents | `Money::fromDecimal(...)->amountInCents` or a hook setter |
| Model → view | cents → decimal | `$model->unitPrice->toDecimal()` or `/ 100` |
| Model → API | cents | resources return integers as stored |

Examples in the codebase:

```php
// BOQ import
$unitPrice = Money::fromDecimal((float) ($row['unit_price'] ?? 0), $currency);

// Billing form
retentionRate: (float) $this->retention_rate / 100,
otherDeductions: (int) round((float) $this->other_deductions * 100),
```

## Currency

- `companies.currency` is the tenant currency (`USD`, `EUR`, `GBP`, `CAD`, `AUD`, `NZD`, `PHP`).
- Documents store their own currency column (e.g. `contract_value_currency`, `currency` on
  BOQ items, billings, budgets).
- **There is no FX conversion anywhere.** Changing the company currency relabels; it does
  not convert historical values. Mixing currencies on one project will produce nonsense
  margins.

## Rounding

- Inputs are rounded to the nearest cent on the way in.
- Line totals are `(int) round(quantity * unit_price)`.
- Percentages (retention, tax) are applied then rounded once.
- Never round twice — compute in cents, round at the end.

## Testing money

```php
it('stores unit price in minor units', function () {
    $item = BoqItem::factory()->create(['unit_price' => 12345, 'currency' => 'PHP']);

    expect($item->unitPrice->toDecimal())->toBe(123.45)
        ->and($item->unitPrice->currency)->toBe('PHP');
});
```
