---
title: Roles and permissions
sidebar_position: 2
description: The seeded staff roles, the modules each can use, and how permissions are enforced.
---

# Roles and permissions

StructCRM uses coarse-grained permissions (`manage_clients`, `manage_boq`, …) grouped into
roles with `spatie/laravel-permission`. A user has exactly one role in practice.

## Staff roles

| Role | Typical person | Modules they can change |
| :--- | :--- | :--- |
| **Owner** | Founder, managing director | Everything |
| **Administrator** | Operations manager | Everything, including team and marketing |
| **Project Manager** | Runs projects end to end | Projects, contracts, BOQ, budgets, progress reports, billings, tasks, estimates, change orders, invoices, clients, leads, documents, photos, daily logs, time entries |
| **Quantity Surveyor** | Commercial control | Projects, contracts, cost codes, BOQ, budgets, progress reports, billings, change orders, clients, documents |
| **Engineer** | Technical supervisor | Documents, photos, daily logs |
| **Foreman** | Site crew leader | Photos, daily logs, time entries |
| **Staff** | Crew member | Time entries |
| **Accountant** | Finance | Invoices, billings, payments, expenses, purchase orders, suppliers |

### Read vs write

- **Reading** only requires that the record belongs to your company. Most staff can see
  most modules.
- **Creating, updating and deleting** requires the matching `manage_*` permission.
- Deleting a record with no delete permission is refused even if you created it.

### The Quantity Surveyor role

The role and its permissions are seeded, but the **Team** screen only offers the other
seven roles. Assign it with a console command or through the API (see below) if you need
it.

## Changing someone's role

An administrator or owner opens **Team**, picks the new role from the row menu and saves.
Rules enforced by the application:

- You cannot change your own role.
- Only an owner can assign or change the `owner` role, and only an owner can invite
  another owner.
- Non-owners cannot see the owner option at all.

Role changes apply immediately — no re-login needed.

## External users

Client portal users are not staff and have no role. They are `users` rows with
`type = client`, linked to one client record, authenticated by the separate `client`
guard. They can only see that client's projects, change orders and invoices.

## Enforcement points

| Layer | What it checks |
| :--- | :--- |
| Model policies | `view` requires the same `company_id`; `create`/`update`/`delete` require the module permission |
| Form requests | `authorize()` delegates to the policy before validation runs |
| API controllers | Call `authorize()` explicitly on show/update/delete |
| AI tools | Same policies as the web UI; the assistant reports a missing permission instead of working around it |

## Adding a new role

Permissions are seeded by `database/seeders/RolePermissionSeeder.php`. To add a role:

```php
$this->role('estimator', ['manage_estimates', 'manage_clients', 'manage_projects']);
```

Then add it to `Team` and `RolePermissionSeeder::PERMISSIONS` if it needs a new
permission. See [Authorization](/docs/developer-guide/authorization).
