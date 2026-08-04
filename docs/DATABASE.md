# Database Design

## Core Tables

-   companies
-   users
-   clients
-   projects
-   estimates
-   estimate_items
-   tasks
-   daily_logs
-   project_photos
-   invoices
-   expenses
-   suppliers
-   materials
-   purchase_orders
-   employees

## Relationships

-   Company has many Users
-   Company has many Clients
-   Client has many Projects
-   Project has many Tasks
-   Project has many Daily Logs
-   Project has many Photos
-   Project has many Estimates
-   Estimate has many Estimate Items

## Standards

-   Foreign keys
-   Soft deletes where appropriate
-   UUID-ready
-   Timestamps on all tables
