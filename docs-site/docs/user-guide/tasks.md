---
title: Tasks
sidebar_position: 9
description: Track work items, priorities and assignments across projects.
---

# Tasks

Tasks are the lightweight work tracker: assignable, datable, and grouped by project.
Requires `manage_tasks`.

## Fields

| Field | Required | Notes |
| :--- | :--- | :--- |
| Title | Yes | |
| Project | Yes | |
| Description | No | Detail, acceptance criteria |
| Status | Yes | `to_do`, `in_progress`, `in_review`, `done` |
| Priority | Yes | `low`, `medium`, `high`, `urgent` |
| Assigned to | No | Any staff user in your company |
| Due date | No | |

## Working with tasks

- The list is newest first and shows status, priority, assignee and due date.
- Selecting a project in the top bar filters the list to that project's tasks.
- There is no Kanban board or dependency graph; tasks are a flat checklist per project.

## Statuses and priorities

| Status | Meaning |
| :--- | :--- |
| `to_do` | Not started |
| `in_progress` | Being worked on |
| `in_review` | Waiting for inspection/sign-off |
| `done` | Complete |

Priority is purely informative — it does not affect sorting in the list beyond being shown.

## Reporting reality

Task completion does **not** feed project progress, billing or the dashboard. Progress is
driven by [progress reports](/docs/user-guide/progress-reports) against the BOQ. Keep tasks
as an internal coordination tool.
