---
title: Components and views
sidebar_position: 9
description: How pages are built with single-file Livewire components, layouts and Blade UI components.
---

# Components and views

Pages are **single-file Livewire components**: the PHP class and the Blade template live in
the same `.blade.php` file under `resources/views/livewire/`. This is the Volt-style API
shipped with Livewire 4 — the `livewire/volt` package is **not** installed and not needed.

## Anatomy

```php
<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function with(): array
    {
        return [
            'leads' => Lead::query()
                ->where('company_id', auth()->user()->company_id)
                ->when($this->search !== '', fn ($q) => $q->where('first_name', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <x-page-header title="Leads" description="Track prospective work." />
    <x-table>…</x-table>
</div>
```

**Use the attribute, not the helper.** `#[Layout('layouts.app')]` works on class-based
components; the `layout()` helper only works in functional components.

## Conventions

| Concern | Convention |
| :--- | :--- |
| File | `resources/views/livewire/<module>/<page>.blade.php` |
| Route | `Route::livewire('module/page', 'module.page')` |
| Data for the view | Return it from `with()` |
| Filters | `#[Url] public string $search = ''` — keeps results shareable |
| Pagination | `->paginate(15)` with `use WithPagination` |
| Flash messages | `session()->flash('status', 'Saved.')`, rendered by the layout |
| Authorisation | `mount()` or the action method, never the Blade template alone |
| Redirects | `$this->redirect(route('invoices.index'), navigate: true)` |

## Layouts

| Layout | Used by |
| :--- | :--- |
| `layouts.app` | Every authenticated staff page — top bar, project switcher, navigation, flash region |
| `layouts.guest` | Login, register, password reset |

## Blade UI components

`resources/views/components` provides the design system. Reuse these instead of writing new
markup:

| Component | Purpose |
| :--- | :--- |
| `x-page-header` | Title, description, `actions` slot |
| `x-table`, `x-table.th`, `x-table.td` | Data tables |
| `x-badge` with `tone` | Status pills (`green`, `amber`, …) |
| `x-button`, `x-secondary-button`, `x-danger-button` | Actions |
| `x-card` | Content blocks |
| `x-input-label`, `x-text-input`, `x-textarea`, `x-select`, `x-input-error` | Forms |
| `x-search-input` | Debounced search bound to `search` |
| `x-modal`, `x-confirm-delete` | Confirmations |
| `x-empty-state` | Empty lists |
| `x-toast`, `x-action-message` | Transient feedback |
| `x-spinner`, `x-skeleton` | Loading states |

## Alpine and repeaters

Alpine ships with Livewire. Line-item repeaters use Alpine for the add/remove UI and
Livewire for the data:

```blade
<template x-for="(item, index) in items">
    <input type="text" x-model="items[index].description">
</template>
```

When you add rows client-side, make sure the Livewire property is the source of truth
before submitting — a common source of "my line items vanished" bugs.

## The project switcher

`resources/views/livewire/layout/navigation.blade.php` stores the selected project in the
session (`active_project_id`). Sixteen index pages and their create forms read it:

```php
->when(session('active_project_id'), fn ($query) =>
    $query->where('project_id', session('active_project_id')))
```

If you add a project-scoped module, follow the same pattern so it behaves consistently.

## Static pages

`Route::view('/', 'welcome')` serves the public marketing page from
`resources/views/welcome.blade.php` and needs no component.
