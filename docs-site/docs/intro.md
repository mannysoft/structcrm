---
title: Introduction
sidebar_position: 1
slug: /intro
description: What StructCRM is, who it is for, what ships today, and how this documentation is organised.
---

# Introduction

**StructCRM** is an open-source, multi-tenant **construction project management and CRM**
platform built with Laravel. It replaces spreadsheets, chat threads and paper logs with a
single application for the office and the field: a lead pipeline, projects and field
records, and construction-specific commercial control (BOQ, budgets, progress reports and
progress billings).

It is designed for small and mid-sized general contractors, specialty subcontractors,
builders and quantity surveyors — companies that need real cost control without the
overhead of an enterprise suite.

## Who this documentation is for

| You are… | Start here |
| :--- | :--- |
| Evaluating StructCRM | This page, then [Getting Started](/docs/getting-started/requirements) |
| Running a company inside it | [User Guide](/docs/user-guide/overview) |
| Extending or self-hosting it | [Developer Guide](/docs/developer-guide/architecture) |
| Integrating another system | [REST API](/docs/api/authentication) |
| Operating a deployment | [Operations](/docs/operations/environment-variables) |
| Contributing | [Contributing](/docs/contributing) |

## What ships today

| Area | Capabilities |
| :--- | :--- |
| **Company & team** | Single-database multi-tenancy enforced by a global tenant scope, staff invitations by email, seven staff roles with granular `manage_*` permissions |
| **CRM** | Clients, leads with a status pipeline, suppliers |
| **Projects & field** | Projects, tasks, daily logs, site photos, documents, time tracking, equipment, maintenance logs |
| **Commercial** | Contracts, cost codes, bills of quantities (manual or Excel import), budgets and budget revisions, progress reports, progress billings and payments, estimates |
| **Finance** | Invoices, expenses, change orders with approval workflow, purchase orders |
| **Client portal** | A separate external login where clients see progress, approve or reject change orders and mark invoices paid |
| **AI assistant** | A conversational agent with tools across every module, human approval for destructive or sensitive actions |
| **REST API** | Sanctum token authentication, JSON endpoints for every module |
| **Marketing** | Email campaigns with sequenced steps, consent tracking and unsubscribe links |

## Tech stack

| Layer | Technology |
| :--- | :--- |
| Language | PHP 8.4+ |
| Framework | Laravel 13 |
| UI | Livewire 4 with class-based full-page Volt-style components |
| Styling | Tailwind CSS 4, Vite |
| Database | SQLite (default, used by tests) or MySQL |
| Auth | Laravel Breeze session auth for staff, a dedicated `client` guard for the portal, Sanctum tokens for the API |
| Permissions | `spatie/laravel-permission` |
| AI | `laravel/ai` agents and tools |
| Spreadsheets | `maatwebsite/excel` for BOQ import |
| Mail | Log driver locally, Resend in production |
| Tests | Pest 4, Laravel Dusk for browser tests |

## What is **not** implemented yet

The product requirements document lists several capabilities that the code does not
implement today. They are documented here so nobody is surprised:

- Stripe / Laravel Cashier subscription billing (companies, plans, trials).
- Redis and Horizon — cache, sessions, queues and jobs all use the database by default.
- Drag-and-drop Kanban task board (tasks are managed through list views).
- Amazon S3 is supported by the filesystem configuration but not wired into uploads by
  default; uploads use the `public` disk unless you change `FILESYSTEM_DISK`.
- PDF rendering and emailing of invoices/estimates.

Contributions in any of these areas are welcome — see [Contributing](/docs/contributing).

## Repository layout

```text
app/
├── Ai/                 # Assistant agent, tools and analytics
├── Common/             # Shared traits, scopes and value objects (Money, TenantScope)
├── Domains/            # CompanyManagement, ProjectManagement, Finance, Sales, Marketing
├── Http/               # Middleware, auth controllers, AI controller
├── Livewire/           # Forms and actions
└── Models/             # User
resources/views/livewire # Full-page components (Volt-style) and layouts
routes/web.php           # Web + portal routes
routes/api.php           # Token-authenticated API
```

## License

StructCRM is released under the [MIT license](https://opensource.org/licenses/MIT).
