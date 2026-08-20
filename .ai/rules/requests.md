---
paths:
  - 'app/Domains/**/Requests/*.php'
---

# Requests

## Kebab-case API resources use snake_case route params
For kebab-case apiResource names (cost-codes, boq-items, progress-reports, change-orders, daily-logs, purchase-orders) the route parameter is snake_case (cost_code, boq_item, progress_report, change_order, daily_log, purchase_order). FormRequest authorize()/rules() must read it with $this->route('snake_case'), not camelCase — $this->route('costCode') returns null and yields 403. Controller method args may stay camelCase (Laravel's implicit binding snake-normalizes).
