<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Enums;

/**
 * Staff roles defined by the PRD "User Personas & Permissions Matrix".
 *
 * Capabilities are coarse-grained module permissions. The Owner role always
 * has every capability; other roles carry the capabilities declared below.
 */
enum Role: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case ProjectManager = 'project_manager';
    case Engineer = 'engineer';
    case Foreman = 'foreman';
    case Staff = 'staff';
    case Accountant = 'accountant';

    /**
     * Whether this role grants the given capability.
     */
    public function can(string $capability): bool
    {
        if ($this === self::Owner) {
            return true;
        }

        return in_array($capability, $this->capabilities(), true);
    }

    /**
     * @return list<string>
     */
    private function capabilities(): array
    {
        return match ($this) {
            self::Owner => [],
            self::Administrator => [
                'manage_company',
                'manage_users',
                'manage_projects',
                'manage_tasks',
                'manage_estimates',
                'manage_change_orders',
                'manage_invoices',
                'manage_expenses',
                'manage_purchase_orders',
                'manage_suppliers',
                'manage_clients',
                'manage_leads',
                'manage_documents',
                'manage_photos',
                'manage_daily_logs',
                'manage_time_entries',
                'manage_equipment',
                'manage_maintenance_logs',
            ],
            self::ProjectManager => [
                'manage_projects',
                'manage_tasks',
                'manage_estimates',
                'manage_change_orders',
                'manage_invoices',
                'manage_clients',
                'manage_leads',
                'manage_documents',
                'manage_photos',
                'manage_daily_logs',
                'manage_time_entries',
            ],
            self::Engineer => [
                'manage_documents',
                'manage_photos',
                'manage_daily_logs',
            ],
            self::Foreman => [
                'manage_photos',
                'manage_daily_logs',
                'manage_time_entries',
            ],
            self::Accountant => [
                'manage_invoices',
                'manage_expenses',
                'manage_purchase_orders',
                'manage_suppliers',
            ],
            self::Staff => [
                'manage_time_entries',
            ],
        };
    }
}
