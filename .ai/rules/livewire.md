---
paths:
  - 'resources/views/livewire/**'
---

# Livewire

## Class-based Volt full pages use #[Layout], not layout() helper
Full-page Volt components written with the class-based syntax (`new class extends Component`) must set their layout via the `#[Layout('layouts.app')]` attribute — e.g. `new #[Layout('layouts.app')] class extends Component {}`. The Volt `layout('layouts.app')` helper function only works with the functional-style syntax (like the Breeze auth pages).
