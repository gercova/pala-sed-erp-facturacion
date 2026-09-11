<?php

namespace Database\Seeders;

use App\Models\Cash;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCashId = (int) (Cash::query()->orderBy('id')->value('id') ?? 1);
        $warehouseIds = Warehouse::query()->orderBy('id')->pluck('id')->all();
        $primaryWarehouseId = (int) ($warehouseIds[0] ?? 1);
        $secondaryWarehouseId = (int) ($warehouseIds[1] ?? $primaryWarehouseId);

        $superAdmin = User::updateOrCreate(
            ['user' => 'admin'],
            [
                'nombres' => 'KROWED NAJAR',
                'password' => 'admin123$$.',
                'estado' => 1,
                'idcaja' => $defaultCashId,
                'idalmacen' => $primaryWarehouseId,
            ]
        );
        $superAdmin->syncRoles(['SUPERADMIN']);
        $superAdmin->warehouses()->sync([$primaryWarehouseId]);

        $admin = User::updateOrCreate(
            ['user' => 'testuser'],
            [
                'nombres' => 'TEST USER',
                'password' => 'Test1234$$.',
                'estado' => 1,
                'idcaja' => $defaultCashId,
                'idalmacen' => $primaryWarehouseId,
            ]
        );
        $admin->syncRoles(['ADMIN']);
        $admin->warehouses()->sync([$primaryWarehouseId]);

        $vendedor = User::updateOrCreate(
            ['user' => 'ventas'],
            [
                'nombres' => 'ADMIN VENTAS',
                'password' => 'ventas123.',
                'estado' => 1,
                'idcaja' => $defaultCashId,
                'idalmacen' => $primaryWarehouseId,
            ]
        );
        $vendedor->syncRoles(['VENDEDOR']);
        $vendedor->warehouses()->sync(array_values(array_unique([$primaryWarehouseId, $secondaryWarehouseId])));

        $cajero = User::updateOrCreate(
            ['user' => 'cajero'],
            [
                'nombres' => 'CAJERO PRUEBA',
                'password' => 'cajero123.',
                'estado' => 1,
                'idcaja' => $defaultCashId,
                'idalmacen' => $primaryWarehouseId,
            ]
        );
        $cajero->syncRoles(['CAJERO']);
        $cajero->warehouses()->sync([$primaryWarehouseId]);

        $contabilidad = User::updateOrCreate(
            ['user' => 'conta'],
            [
                'nombres' => 'CONTABILIDAD PRUEBA',
                'password' => 'conta123.',
                'estado' => 1,
                'idcaja' => $defaultCashId,
                'idalmacen' => $primaryWarehouseId,
            ]
        );
        $contabilidad->syncRoles(['CONTABILIDAD']);
        $contabilidad->warehouses()->sync([$primaryWarehouseId]);
    }
}
