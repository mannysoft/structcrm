# Architecture Guide

## Stack

-   Laravel 12
-   PHP 8.4+
-   Livewire 3
-   Tailwind CSS 4
-   MySQL
-   Redis
-   Horizon

## Layers

-   Controllers
-   Services
-   Actions
-   Repositories (when beneficial)
-   Models
-   Policies
-   Form Requests
-   Jobs
-   Events
-   Notifications

## Principles

-   SOLID
-   Clean Architecture
-   Dependency Injection
-   Thin Controllers
-   No business logic in Blade

## Testing

-   Pest PHP
-   Feature tests
-   Unit tests

## Multi-tenancy

Every table that belongs to a tenant references company_id.
Authorization must always scope data to the authenticated company.
