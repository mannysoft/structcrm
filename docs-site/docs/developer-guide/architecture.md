---
title: Architecture
sidebar_position: 1
description: The layers, the request lifecycle, and the rules that keep domain logic out of the UI.
---

# Architecture

StructCRM follows a **thin presentation, action-oriented** layered architecture: Livewire
components render and validate; single-purpose action classes own business transactions;
models own persistence and relationships.

## The stack

| Layer | Technology |
| :--- | :--- |
| Presentation | Livewire 4 full-page components in `resources/views/livewire` |
| Styling | Tailwind CSS 4 via Vite |
| Application | Laravel 13, PHP 8.4 |
| Domain | `app/Domains/*` — actions, DTOs, enums, models, policies, requests, resources |
| Shared | `app/Common` — `BelongsToTenant`, `TenantScope`, `Money` |
| AI | `app/Ai` — one agent with ~140 tools |
| Data | SQLite or MySQL; sessions, cache and queue can all use the database |

## Request lifecycle

```text
HTTP request
   │
   ├─ middleware: auth, verified, getting-started (create routes)
   │
   ├─ Livewire full-page component   (resources/views/livewire/<module>/<page>.blade.php)
   │     • validates input
   │     • authorises via policies
   │     • maps input into a DTO
   │
   ├─ Action class                   (app/Domains/<Domain>/Actions/*Action.php)
   │     • DB::transaction
   │     • writes models
   │     • observers assign document numbers
   │     • dispatches events / notifications / jobs
   │
   └─ Redirect + session flash

JSON request (routes/api.php)
   │
   ├─ auth:sanctum + throttle
   ├─ Controller (thin) → FormRequest (authorise + validate + toDTO)
   ├─ Action
   └─ API Resource (UUID as `id`)
```

## Layer responsibilities

| Layer | Owns | Must not |
| :--- | :--- | :--- |
| Livewire component | Rendering, form state, user-facing messages | Contain multi-step business rules or raw financial maths |
| Form request (`Requests/`) | Authorisation + validation + mapping to a DTO | Query other domains |
| DTO (`DTOs/`) | A typed, immutable argument object | Contain behaviour |
| Action (`Actions/`) | One business transaction, wrapped in `DB::transaction` | Render, redirect, or read the request |
| Model | Relationships, casts, scopes, money hooks | Send mail or authorise users |
| Policy (`Policies/`) | Tenant + permission checks for one model | Contain queries |
| Observer | Automatic derived data (document numbers) | Contain business decisions |
| Resource (`Resources/`) | API serialisation (UUID ids, ISO dates) | Leak internal integer ids |

## Design decisions you will notice

- **Actions instead of fat controllers or model events.** Every write path (web, API, AI
  tool) calls the same action, so a change order approved from the portal behaves
  identically to one approved by the assistant.
- **Enums instead of strings.** Every status is a backed enum under
  `Domains/<Domain>/Enums`, cast on the model, validated with `Rule::enum`.
- **Money as integers.** All amounts are stored in minor units and exposed through `Money`
  property hooks. See [Money and currency](/docs/developer-guide/money-and-currency).
- **UUIDs on the outside, integers inside.** Models expose `uuid` (route key and API id)
  while relationships use integer keys.
- **Two auth guards.** Staff use the `web` guard; portal clients use `client`. The AI
  assistant and all staff routes are staff-only.
- **One AI agent, many tools.** `CrmAssistant` composes tool classes grouped by domain, each
  of which calls an action and respects the same policies.

## Where to look first

| You want to… | Read |
| :--- | :--- |
| Understand a module | [Domain modules](/docs/developer-guide/domain-modules) |
| Add a field safely | [Validation and DTOs](/docs/developer-guide/validation-and-dtos) |
| Handle money | [Money and currency](/docs/developer-guide/money-and-currency) |
| Avoid tenant leaks | [Multi-tenancy](/docs/developer-guide/multi-tenancy) |
| Change UI | [Volt components](/docs/developer-guide/volt-components) |
| Add a module end to end | [Adding a module](/docs/developer-guide/adding-a-module) |
