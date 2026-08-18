---
paths:
  - 'app/Domains/**'
---

# Domains

## Override uniqueIds() on integer-id + uuid domain models
Domain models use an auto-increment integer `id` primary key plus a unique `uuid` column. Using the `HasUuids` trait without an override makes uniqueIds() return ['id'], which UUIDs the integer PK and breaks inserts. Always add: public function uniqueIds(): array { return ['uuid']; }.
