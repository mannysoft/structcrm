<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * The coarse-grained module permissions mirrored by the policy gates.
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
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
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        $this->role('owner', self::PERMISSIONS);
        $this->role('administrator', self::PERMISSIONS);
        $this->role('project_manager', [
            'manage_projects', 'manage_tasks', 'manage_estimates', 'manage_change_orders',
            'manage_invoices', 'manage_clients', 'manage_leads', 'manage_documents',
            'manage_photos', 'manage_daily_logs', 'manage_time_entries',
        ]);
        $this->role('engineer', ['manage_documents', 'manage_photos', 'manage_daily_logs']);
        $this->role('foreman', ['manage_photos', 'manage_daily_logs', 'manage_time_entries']);
        $this->role('staff', ['manage_time_entries']);
        $this->role('accountant', ['manage_invoices', 'manage_expenses', 'manage_purchase_orders', 'manage_suppliers']);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function role(string $name, array $permissions): Role
    {
        $role = Role::findOrCreate($name);

        $role->syncPermissions($permissions);

        return $role;
    }
}
