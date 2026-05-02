<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Clients
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',

            // Contracts
            'contracts.view', 'contracts.create', 'contracts.edit',

            // Suppliers
            'suppliers.view', 'suppliers.create', 'suppliers.edit',

            // Tasks
            'tasks.view', 'tasks.create', 'tasks.close',

            // Applications
            'applications.view', 'applications.create', 'applications.edit',
            'applications.change_status',

            // Transportations
            'transportations.view', 'transportations.create',
            'transportations.edit', 'transportations.complete',

            // Vehicles
            'vehicles.view', 'vehicles.create', 'vehicles.edit',

            // Drivers
            'drivers.view', 'drivers.create', 'drivers.edit',

            // Owners
            'owners.create', 'owners.edit',

            // Dictionaries
            'dict.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
        }

        // doc_manager
        $docManager = Role::firstOrCreate(['name' => 'doc_manager', 'guard_name' => 'sanctum']);
        $docManager->syncPermissions([
            'dashboard.view',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'contracts.view', 'contracts.create', 'contracts.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'tasks.view', 'tasks.close',
            'applications.view',
            'transportations.view', 'transportations.create',
            'vehicles.view', 'vehicles.create', 'vehicles.edit',
            'drivers.view', 'drivers.create', 'drivers.edit',
            'owners.create', 'owners.edit',
            'dict.manage',
        ]);

        // client_manager
        $clientManager = Role::firstOrCreate(['name' => 'client_manager', 'guard_name' => 'sanctum']);
        $clientManager->syncPermissions([
            'dashboard.view',
            'clients.view', 'contracts.view',
            'applications.view', 'applications.create', 'applications.edit',
            'applications.change_status',
            'tasks.view', 'tasks.create',
            'transportations.view',
            'vehicles.view', 'drivers.view',
            'suppliers.view',
        ]);

        // logistic_manager
        $logisticManager = Role::firstOrCreate(['name' => 'logistic_manager', 'guard_name' => 'sanctum']);
        $logisticManager->syncPermissions([
            'dashboard.view',
            'applications.view',
            'transportations.view', 'transportations.create',
            'transportations.edit', 'transportations.complete',
            'vehicles.view', 'vehicles.create', 'vehicles.edit',
            'drivers.view', 'drivers.create', 'drivers.edit',
            'owners.create', 'owners.edit',
            'suppliers.view', 'clients.view', 'contracts.view',
        ]);

        // admin — all permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin->syncPermissions(Permission::all());
    }
}
