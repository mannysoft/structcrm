---
paths:
  - 'tests/**'
---

# Tests

## Tests use Pest — not PHPUnit classes
This app runs Pest (on top of PHPUnit). Write tests in Pest's functional style — `test()` / `it()` closures with `expect()` assertions. The base `Tests\TestCase` and `RefreshDatabase` are applied to every test via `tests/Pest.php`; do not define `class X extends TestCase` in test files. Convert any PHPUnit-style classes to Pest.
