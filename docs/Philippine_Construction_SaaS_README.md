# Philippine Construction Project Profitability Platform

## Working Title

**Construction Business OS for Philippine Contractors**

### Positioning

> **From Bid to Profit.**

Alternative positioning:

> **Know your cost. Bill faster. Protect your profit.**

The product is a construction-focused SaaS platform designed for Philippine small and medium-sized contractors.

The core idea is not to build another generic CRM. The platform should connect the financial and operational workflow from:

**BOQ → Budget → Actual Cost → Change Orders → Progress → Billing → Collection → Profit**

---

# 1. Product Vision

Help Philippine construction companies answer four critical questions:

1. **Where is my money?**
2. **Which project is losing money?**
3. **What can I bill right now?**
4. **What has not been paid yet?**

The platform should provide a single source of truth for project profitability, billing, cost control, and change orders.

---

# 2. Research Findings

Research into Philippine construction workflows suggests three especially painful workflows:

| Rank | Workflow | Pain | Money Impact | Product Opportunity |
|---|---|---|---|---|
| 1 | Progress Billing → Collection | Very High | Very High | Excellent |
| 2 | BOQ/Budget → Actual Cost → Profitability | Very High | Very High | Excellent |
| 3 | Change Orders → Approval → Billing | High | Very High | Excellent |

These workflows are tightly connected and should form the foundation of the product.

---

# 3. Core Product Loop

```text
                    CONTRACT
                       |
                       v
                    BOQ/BUDGET
                       |
             +---------+---------+
             |                   |
             v                   v
        ACTUAL COST        CHANGE ORDERS
             |                   |
             |                   v
             |             REVISED BUDGET
             |                   |
             +---------+---------+
                       |
                       v
                PROJECT PROGRESS
                       |
                       v
                PROGRESS BILLING
                       |
                       v
                   COLLECTION
                       |
                       v
                    PROFIT
```

---

# 4. MVP Scope

Do NOT attempt to build a complete ERP initially.

The first version should focus on:

## Module 1 — Project Financial Control

- Projects
- Contracts
- BOQ
- Cost codes
- Budget
- Actual costs
- Forecast costs
- Project profitability
- Profit margin
- Cost variance alerts

## Module 2 — Change Orders

- Change order requests
- Scope description
- Additional cost
- Additional revenue
- Attachments
- Client approval
- Revised contract value
- Revised budget
- Change order history
- Change order billing

## Module 3 — Progress Billing

- Project accomplishment
- Billing periods
- Progress billing
- Retention
- Previous billings
- Deductions
- Net billing
- Supporting documents
- Billing status
- Certification
- Due dates
- Payment tracking
- Collection status

---

# 5. Future Modules

These should come after MVP validation.

### Procurement

- Suppliers
- Material requests
- Purchase orders
- Purchase receiving
- Supplier invoices
- Supplier payment tracking

### Inventory

- Warehouse inventory
- Project inventory
- Material transfers
- Material consumption
- Stock alerts

### Subcontractors

- Subcontractor contracts
- Scope
- Progress
- Billing
- Retention
- Payments
- Compliance documents

### Site Management

- Daily site reports
- Site photos
- Site issues
- Weather
- Workers on site
- Work accomplishment

### Workforce

- Employees
- Attendance
- Project assignment
- Labor cost
- Payroll integration

### Client Portal

- Project progress
- Photos
- Documents
- Change orders
- Billing
- Payment history

### CRM

- Leads
- Contacts
- Opportunities
- Bids
- Proposals
- Follow-ups

---

# 6. Killer Feature — BOQ → Everything

The strongest product concept is an Excel-first BOQ workflow.

A contractor uploads an Excel BOQ.

The system converts it into structured project data.

```text
                    EXCEL BOQ
                        |
            +-----------+-----------+
            |           |           |
            v           v           v
        ESTIMATE      BUDGET     MATERIALS
            |                       |
            v                       v
        PROPOSAL                    PO
                                    |
                                    v
                                SUPPLIER
```

After the project begins:

```text
BOQ
 |
 +--> Actual Purchases
 |
 +--> Labor
 |
 +--> Equipment
 |
 +--> Subcontractor Costs
 |
 +--> Other Expenses
 |
 v
Actual Project Cost
 |
 v
Project Profitability
```

This creates a direct relationship between what was estimated and what actually happened.

---

# 7. Progress Billing Workflow

Recommended workflow:

```text
Site Accomplishment
        |
        v
Billing Preparation
        |
        v
Draft
        |
        v
Submitted
        |
        v
Under Review
        |
        v
Approved
        |
        v
Certified
        |
        v
Due
        |
        v
Partially Paid
        |
        v
Paid
```

Example:

```text
Contract Value:          ₱20,000,000
Previous Billing:         ₱7,000,000
Current Accomplishment:   ₱2,500,000
Retention:                  ₱250,000
Other Deductions:           ₱100,000
--------------------------------------
Net Billing:              ₱2,150,000
```

Every billing amount should be traceable back to the BOQ and accomplishment data.

---

# 8. Project Profitability

The system should continuously calculate:

- Original contract value
- Change orders
- Revised contract value
- Original budget
- Revised budget
- Actual cost
- Remaining forecast cost
- Projected final cost
- Projected profit
- Projected margin
- Cost variance
- Revenue collected
- Outstanding billing

Example:

```text
Contract Value             ₱15.0M
Original Budget            ₱12.0M
Actual Cost                 ₱7.2M
Remaining Forecast          ₱5.5M
Projected Final Cost       ₱12.7M
Projected Profit            ₱2.3M
Projected Margin              15.3%
```

The system should warn the user when margins deteriorate.

Example:

> ⚠️ Project margin dropped from 20% to 15.3%.

---

# 9. Change Order Workflow

Change orders should protect contractors from doing undocumented extra work.

Example:

```text
CHANGE ORDER #007

Reason:
Client requested additional wall.

Materials:             ₱80,000
Labor:                 ₱45,000
Equipment:             ₱15,000
Overhead:              ₱10,000
Profit:                ₱30,000
--------------------------------
Change Order Value:   ₱180,000
```

Workflow:

```text
Draft
  ↓
Submitted to Client
  ↓
Client Approved
  ↓
Added to Contract
  ↓
Added to BOQ/Budget
  ↓
Added to Progress Billing
```

Attachments should support:

- Photos
- Drawings
- Site instructions
- Written client instructions
- Documents
- Comments

---

# 10. Owner Dashboard

The first screen should focus on money rather than features.

Example:

```text
OWNER DASHBOARD

Active Projects              12
Contract Value          ₱86.4M
Outstanding Billing      ₱8.7M
Overdue Collections      ₱2.1M

Projected Profit         ₱14.2M
Profit at Risk            ₱2.4M

PROJECTS AT RISK

ABC Building              68%   🔴
XYZ Residence              82%   🟢
DEF Warehouse              44%   ⚠️
```

The owner should immediately understand:

- What can be billed
- What is overdue
- Which projects are profitable
- Which projects are at risk
- Where costs are exceeding budget

---

# 11. Competitive Positioning

A major reference product is iDeal CRM.

iDeal focuses heavily on:

```text
Lead
 ↓
Opportunity
 ↓
Bid
 ↓
Proposal
 ↓
Follow-up
 ↓
Won/Lost
```

The proposed product should go further:

```text
Lead
 ↓
Bid
 ↓
Estimate
 ↓
Proposal
 ↓
Won
 ↓
Project
 ↓
Budget
 ↓
Actual Cost
 ↓
Change Orders
 ↓
Progress
 ↓
Billing
 ↓
Collection
 ↓
Profit
```

The positioning should therefore NOT be:

> "Another construction CRM."

Instead:

> **Construction Business OS for Philippine Contractors**

or:

> **Construction Project Profitability Platform**

with:

> **From Bid to Profit.**

---

# 12. Target Customer

Initial target market:

### Small and Medium Philippine Contractors

Ideal characteristics:

- 5–100 employees
- Multiple active projects
- Uses Excel heavily
- Uses Messenger/Viber/WhatsApp for coordination
- Has a QS, project engineer, or site engineer
- Owner is still closely involved in finances
- Has recurring progress billings
- Experiences payment delays
- Has difficulty tracking project profitability
- Has change orders and extra work
- Wants affordable software

Avoid very large enterprise contractors initially because their requirements and procurement cycles can dramatically increase complexity.

---

# 13. User Roles

Initial roles:

### Owner / Admin

Full visibility.

### Project Manager

Project and financial visibility.

### Quantity Surveyor

BOQ, estimates, accomplishment and billing.

### Site Engineer

Progress, site reports, photos and change requests.

### Accountant

Billing, payments, expenses and financial records.

### Procurement

Purchase requests, POs and suppliers.

### Client

Optional portal access.

---

# 14. Suggested Pricing Experiment

Potential Philippine pricing:

## Free

₱0/month

- 1 active project
- Limited users
- BOQ
- Estimate
- Basic project tracking
- Basic profitability

## Starter

₱999/month

- 3 users
- 5 active projects
- CRM
- BOQ
- Estimates
- Basic project management

## Professional

₱2,499/month

- 10 users
- Unlimited projects
- Procurement
- Progress billing
- Expenses
- Profitability
- Client portal

## Business

₱4,999/month

- 25 users
- Unlimited projects
- Advanced reporting
- Multi-project dashboard
- Advanced permissions
- Payroll integration
- API/integrations

## Enterprise

Custom pricing.

Potential services:

- Data migration
- Excel BOQ conversion
- Initial setup
- Training
- Custom integrations

Pricing must be validated with actual Philippine contractors before implementation.

---

# 15. Why Free Can Work

The free plan should let a contractor experience the core value.

Recommended restriction:

**1 active project**

Allow:

- BOQ
- Budget
- Actual costs
- Progress
- Billing
- Profitability

When they attempt to add another project:

> "You've reached your free project limit. Upgrade to manage additional projects."

This creates a natural product-led growth path.

---

# 16. Long-Term Moat

The competitive moat should not be the number of features.

The moat should become **construction-specific data and intelligence**.

Over time the platform could understand:

- Material prices
- Labor costs
- Project margins
- Supplier pricing
- Cost overruns
- Change-order frequency
- Payment delays
- Project durations
- Common budget variances

Future intelligence could say:

> "Your steel cost is 13% above the historical average for similar projects."

or:

> "This project is trending toward a 15% margin compared with its original 20% estimate."

This becomes difficult for generic CRM products to replicate.

---

# 17. AI Roadmap

AI should be added after reliable project data exists.

Potential AI capabilities:

### Project Risk Analysis

> "Which projects are at risk?"

### Profitability Analysis

> "Why is Project ABC losing money?"

### Cost Forecasting

> "What will the final project cost likely be?"

### Billing Assistant

> "What can we bill this month?"

### Change Order Assistant

Generate a change order from a site instruction and estimate.

### Executive Assistant

Answer:

> "How much money is currently outstanding?"

> "Which client pays us the slowest?"

> "Which project has the highest cost variance?"

---

# 18. MVP Database Concepts

Potential core entities:

```text
organizations
users
projects
clients
contracts
boq_items
cost_codes
budgets
budget_items
expenses
expense_items
change_orders
change_order_items
progress_reports
progress_items
billings
billing_items
billing_payments
retentions
documents
suppliers
purchase_orders
purchase_order_items
```

Later:

```text
employees
subcontractors
attendance
inventory
warehouses
stock_movements
material_requests
site_reports
project_tasks
project_photos
client_portal_users
```

---

# 19. Laravel Architecture Direction

Recommended stack based on the project requirements:

### Backend

- PHP
- Laravel
- REST API
- Queues
- Scheduled jobs
- Policies/permissions
- Events/listeners

### Frontend

- Laravel Livewire
- Tailwind CSS

### Database

- MySQL/PostgreSQL

### Storage

- AWS S3

### Infrastructure

- Docker
- Nginx
- AWS or DigitalOcean initially

### Multi-tenancy

Organization/company-based tenancy.

Every major business entity should belong to an organization.

Example:

```text
Organization
    |
    +-- Users
    +-- Clients
    +-- Projects
    +-- Contracts
    +-- BOQ
    +-- Expenses
    +-- Billings
    +-- Suppliers
```

---

# 20. Important Product Principle

Do not build a feature because competitors have it.

Every feature should answer one of these questions:

### Revenue

Does it help the contractor win more work?

### Cost

Does it prevent unnecessary spending?

### Cash

Does it help the contractor bill or collect faster?

### Profit

Does it help the owner protect project margin?

If a feature does none of these, it should probably not be in the MVP.

---

# 21. Initial Validation Plan

Before building the complete SaaS:

### Step 1

Interview 20–30 Philippine contractors.

### Step 2

Ask them to demonstrate their actual workflow.

Do not simply ask:

> "Would you use this software?"

Instead ask:

> "Show me how you prepared your last progress billing."

> "Show me how you track project expenses."

> "Show me your last change order."

> "How do you know if a project is profitable?"

> "What happens when the client delays payment?"

### Step 3

Document:

- Software used
- Excel spreadsheets
- People involved
- Time spent
- Errors
- Delays
- Manual processes
- Financial impact

### Step 4

Identify the most expensive pain.

### Step 5

Build only that workflow first.

### Step 6

Get 3 contractors to use it for free.

### Step 7

Convert those companies into paying customers.

---

# 22. First 90-Day Product Strategy

## Days 1–15

Research contractors.

Interview 20–30 companies.

Map their workflows.

## Days 16–30

Design:

- UX
- Database
- Tenant architecture
- BOQ import
- Project financial model

Create clickable prototype.

## Days 31–60

Build:

- Organizations
- Users
- Projects
- Contracts
- BOQ import
- Budget
- Actual costs
- Change orders
- Basic profitability

## Days 61–75

Build:

- Progress accomplishment
- Progress billing
- Retention
- Payment tracking
- Collection dashboard

## Days 76–90

Pilot with 3–5 contractors.

Measure:

- Time saved
- Billing turnaround
- Cost visibility
- Number of manual spreadsheets eliminated
- Number of change orders captured
- Amount billed/collected
- User engagement

Then decide what to build next based on actual usage.

---

# 23. Success Metrics

The MVP should be measured by business outcomes.

### Primary metric

**Number of projects with accurate real-time profitability**

### Secondary metrics

- Billing preparation time
- Billing approval time
- Collection visibility
- Change orders captured
- Cost variance detected
- Monthly active projects
- Projects using BOQ
- Projects using progress billing
- Free → paid conversion
- Monthly recurring revenue

---

# 24. Product Thesis

The core hypothesis is:

> Philippine contractors do not necessarily need another generic CRM or another project management tool.

They need a system that connects:

**What they agreed to build → what they budgeted → what they actually spent → what changed → what they accomplished → what they billed → what they collected → what they actually made.**

That is the core product.

---

# 25. Recommended Product Name Direction

Working names can be explored later.

Potential concepts:

- BuildProfit
- BuildFlow
- Contrax
- BuildOS
- ProjectProfit
- ConstructFlow
- BuildLedger
- BuildTrack
- BuildPilot

The final brand should be researched for Philippine domain availability, trademarks and competing products before adoption.

---

# 26. Final Recommendation

Do not start by trying to beat iDeal CRM feature-for-feature.

Use iDeal as evidence that construction-specific SaaS can be viable.

Then differentiate by owning the **post-sale financial workflow**:

```text
BID
 ↓
BOQ
 ↓
BUDGET
 ↓
PROJECT
 ↓
ACTUAL COST
 ↓
CHANGE ORDER
 ↓
PROGRESS
 ↓
BILLING
 ↓
COLLECTION
 ↓
PROFIT
```

### The product promise:

> **Know your cost. Bill faster. Protect your profit.**

### The long-term vision:

> **The operating system for Philippine construction companies.**
