---
paths:
  - 'routes/**'
---

# Routes

## Gate create routes with the getting-started middleware
Create routes that need existing data (client, project) must add `->middleware('getting-started')` and register the route name in EnsureGettingStartedPrerequisites::PREREQUISITES. New checklist steps live in GettingStartedService::STEPS / STEP_KEYS. Dismissal state is per company (`companies.getting_started_dismissed_at`) and never bypasses the gate.
