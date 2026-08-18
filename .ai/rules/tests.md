---
paths:
  - 'tests/**'
---

# Tests

## Tests are PHPUnit classes — Pest is not installed
This app runs PHPUnit 12 only; pestphp/pest is not a Composer dependency. Write tests as PHPUnit classes (namespaced under Tests\, with test_* methods). Convert any Pest-style tests (test()/expect()/uses()) to PHPUnit or the whole suite errors out.
