---
paths:
  - 'resources/views/livewire/**'
  - 'resources/views/livewire/*/index.blade.php'
---

# Livewire

## Class-based Volt full pages use #[Layout], not layout() helper
Full-page Volt components written with the class-based syntax (`new class extends Component`) must set their layout via the `#[Layout('layouts.app')]` attribute — e.g. `new #[Layout('layouts.app')] class extends Component {}`. The Volt `layout('layouts.app')` helper function only works with the functional-style syntax (like the Breeze auth pages).

## List pages use WithPagination + search
Every staff list index page follows the same pattern: component uses `use Livewire\WithPagination;` plus a `#[Url] public string $search = '';` prop and an `updatedSearch()` method calling `$this->resetPage()`. The `with()` method builds the query with `Model::query()->with(...)->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('col', 'like', "%{$this->search}%")->orWhereHas(...)))->latest()->paginate(15)`. The template adds an `<x-search-input>` toolbar after `<x-page-header>` and `{{ $items->links() }}` after `</x-table>`. Pagination uses the published view resources/views/vendor/livewire/tailwind.blade.php (brand-styled).

## Form and line-item repeater pattern
Create/edit pages start with `<x-page-header title="..." description="..." />` then `<form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">` with a footer `<div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">` containing a Cancel link plus `<x-button type="submit">Save</x-button>`. Line-item repeaters use `x-data="{ items: @entangle('items') }"` with `<template x-for="(item, index) in items" :key="index">` and `<x-text-input x-model="item.field" />` (x-model passes through $attributes). The "Add item" button uses `text-brand-600 hover:text-brand-500`. Field errors use `<x-input-error :messages="$errors->get('field')" />`.
