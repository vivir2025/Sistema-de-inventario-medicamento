<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //create permissions
        $arrayOfPermissionNames = [
            // Permisos existentes...
            'view-sales', 'create-sale','destroy-sale','edit-sale',
            'view-reports','view-category','create-category','destroy-category','edit-category',
            'view-products','create-product','edit-product','destroy-product',
            'view-purchase','create-purchase','edit-purchase','destroy-purchase',
            'view-supplier','create-supplier','edit-supplier','destroy-supplier',
            'view-users','create-user','edit-user','destroy-user',
            'view-access-control',
            'view-role','edit-role','destroy-role','create-role',
            'view-permission','create-permission','edit-permission','destroy-permission',
            'view-expired-products','view-outstock-products','backup-app','backup-db','view-settings',
            // Nuevos permisos para clientes
            'view-customers', 'create-customer', 'edit-customer', 'destroy-customer'
        ];

        $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });

        Permission::insert($permissions->toArray());

      // create roles and assign permissions
$role = Role::create(['name' => 'sales-person'])
    ->givePermissionTo([
        // Compras
        'view-purchase', 
        'create-purchase',
        'edit-purchase',    
        'destroy-purchase', 

        // Ventas
        'view-sales', 
        'create-sale',
        'edit-sale',    
        'destroy-sale',

        // Proveedores
        'view-supplier', 
        'create-supplier',
        'edit-supplier',    
        'destroy-supplier',

        // Reportes
        'view-reports',

        // Categorías
        'view-category',
        'edit-category',    
        'destroy-category',

        // Productos
        'view-products', 
        'create-product', 
        'view-outstock-products', 
        'view-expired-products',
        'edit-product',
        'destroy-product',


        // Clientes
        'view-customers', 
        'create-customer',
        'edit-customer',
        'destroy-customer'
    ]);

            
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}