<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClienteRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permiso del portal de cliente
        $permission = Permission::firstOrCreate(
            ['name' => 'cliente.portal', 'guard_name' => 'web'],
            ['descripcion' => 'Acceso al portal de cliente']
        );

        // Crear rol Cliente y asignarle el permiso
        $role = Role::firstOrCreate(
            ['name' => 'Cliente', 'guard_name' => 'web']
        );

        $role->syncPermissions([$permission]);

        $this->command->info('✅ Rol "Cliente" y permiso "cliente.portal" creados correctamente.');
    }
}
