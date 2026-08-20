---
paths:
  - 'app/Domains/**/Models/*.php'
---

# Models

## Money property hooks: name must differ from column; negatives unsupported
A Money property hook reading $this->{same name} recurses. Name the hook differently from the column (e.g. hook `unitPrice` reads `unit_price`; hook `totalAmount` reads `total`). Also Money throws on negative amounts, so don't attach a Money hook to columns that may hold negatives (e.g. change_orders.amount which can be negative) — keep those as raw integer cents + a plain currency column.
