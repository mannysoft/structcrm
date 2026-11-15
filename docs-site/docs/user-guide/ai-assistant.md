---
title: AI assistant
sidebar_position: 28
description: Ask questions and make changes across every module in natural language — and know when it must ask permission.
---

# AI assistant

The assistant is a conversational agent with tools across the whole platform. It answers
questions about your company's data and performs changes on your behalf. Staff only — the
assistant refuses client portal users.

Open it from **Overview → AI Assistant** (`/assistant`), from the dashboard quick-ask box,
or through the slide-over drawer.

## What it can do

| Area | Examples |
| :--- | :--- |
| **Analytics** | "How is the business doing?" "Which projects have the worst margin?" "Break down expenses by category" |
| **Search** | "Find anything matching 'Meridian Tower'" |
| **Sales** | Clients, leads, estimates, invoices, change orders, purchase orders, suppliers, expenses |
| **Projects** | Projects, tasks, daily logs, photos, documents, time entries, equipment, maintenance logs |
| **Finance** | Cost codes, contracts, BOQ items, budgets, progress reports, billings and payments |
| **Company** | Read the profile, list team members, invite staff, change roles |
| **Marketing** | List/create/update campaigns, read email logs and statistics |

Each record type supports list, get, create, update and delete. Detail tools return line
items, so the assistant reads before editing.

## How it works

- It always uses a tool rather than inventing values — it will not guess a UUID or an
  amount.
- It resolves records by their human number too (`INV-12`, `CO-3`, `PRJ-2026-001`).
- It asks a short clarifying question when the request is ambiguous.
- It respects your permissions. If you lack `manage_invoices`, it says so instead of
  working around it.
- It only ever sees your company's records.
- Amounts are handled in major units in the conversation and stored in minor units behind
  the scenes, with your company currency.

## Human approval

Some actions are irreversible or grant access, so they pause for your explicit decision:

| Requires approval |
| :--- |
| Every delete (projects, clients, invoices, estimates, change orders, suppliers, BOQ items, budgets, contracts, cost codes, progress reports, billings, photos, documents, daily logs, time entries, tasks, equipment, maintenance logs, marketing campaigns) |
| Approving or rejecting a change order |
| Recording a billing payment |
| Inviting a client portal user |
| Inviting a staff user |
| Changing a team member's role |

When an action needs approval the assistant stops and shows the pending action with its
reason. Choose **approve** or **reject**, then resume the turn. Nothing runs until you
decide, and the assistant will not claim an action happened while it is waiting.

## Conversations

- The most recent conversation loads automatically when you open the page.
- You can browse earlier conversations from the history panel and start a new one.
- Conversations are stored per user; nobody else can read yours.

## Configuration

The assistant needs an AI provider key (`DEEPSEEK_API_KEY`, `OPENAI_API_KEY`, …) and uses
`config/ai.php` defaults. Without a key, the page returns an error but the rest of the
application is unaffected. See [Configuration](/docs/getting-started/configuration#ai).

## Tips for good results

- Name the project or document number when you know it.
- Ask for one change at a time; approval prompts are easier to review that way.
- Ask it to list before it updates, so you can confirm you are acting on the right record.
- Treat its arithmetic as a summary, not as your books — verify anything you will act on
  financially.
