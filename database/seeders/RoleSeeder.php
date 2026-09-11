<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'SUPERADMIN']);
        $admin = Role::firstOrCreate(['name' => 'ADMIN']);
        $vendedor = Role::firstOrCreate(['name' => 'VENDEDOR']);
        $cajero = Role::firstOrCreate(['name' => 'CAJERO']);
        $contabilidad = Role::firstOrCreate(['name' => 'CONTABILIDAD']);

        $permissionsByModule = [
            'Dashboard' => [
                'admin.home' => 'Acceder al dashboard principal',
            ],
            'Ventas' => [
                'admin.clients' => 'Gestionar clientes',
                'admin.quotes' => 'Gestionar cotizaciones',
                'admin.pos' => 'Usar punto de venta',
                'admin.sale_notes' => 'Gestionar notas de venta',
                'admin.billings' => 'Gestionar comprobantes',
                'admin.shipment_guides' => 'Gestionar guias de remision',
            ],
            'Compras' => [
                'admin.providers' => 'Gestionar proveedores',
                'admin.buys' => 'Gestionar compras',
            ],
            'Inventario' => [
                'admin.products' => 'Gestionar productos',
                'admin.categories' => 'Gestionar categorias',
                'admin.warehouses' => 'Gestionar almacenes',
                'admin.transfer_orders' => 'Gestionar ordenes de traslado',
            ],
            'Operaciones' => [
                'admin.arching_cashes' => 'Gestionar arqueo de cajas',
            ],
            'Configuracion' => [
                'admin.business' => 'Gestionar empresa',
                'admin.paymodes' => 'Gestionar metodos de pago',
                'admin.cashes' => 'Gestionar cajas',
                'admin.series' => 'Gestionar series',
                'admin.users' => 'Gestionar usuarios',
                'admin.roles' => 'Gestionar roles y permisos',
            ],
            'Reportes' => [
                'report.sales.index' => 'Ver reporte de ventas',
                'report.sales.by_product.index' => 'Ver reporte de ventas por producto',
                'report.payments.index' => 'Ver reporte de pagos',
                'report.billings.sales_register' => 'Ver registro de ventas',
                'report.billings.billing_documents' => 'Ver reporte de documentos emitidos',
                'report.billings.credit_notes' => 'Ver reporte de notas de credito',
            ],
            'Distribución' => [
                'admin.deliveries' => 'Gestionar despachos y delivery',
                'admin.jug_movements' => 'Gestionar control de envases retornables',
                'admin.loyalty' => 'Gestionar promociones de fidelización',
            ],
        ];

        $repartidor = Role::firstOrCreate(['name' => 'REPARTIDOR']);

        $allPermissions = [];

        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name],
                    ['descripcion' => $description]
                );

                if (empty($permission->descripcion)) {
                    $permission->descripcion = $description;
                    $permission->save();
                }

                $allPermissions[] = $permission->name;
            }
        }

        $superAdmin->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);

        $vendedor->syncPermissions([
            'admin.home',
            'admin.clients',
            'admin.quotes',
            'admin.pos',
            'admin.sale_notes',
            'admin.billings',
            'admin.shipment_guides',
            'admin.arching_cashes',
        ]);

        $cajero->syncPermissions([
            'admin.home',
            'admin.pos',
            'admin.sale_notes',
            'admin.billings',
            'admin.arching_cashes',
        ]);

        $contabilidad->syncPermissions([
            'admin.home',
            'report.billings.sales_register',
            'report.billings.billing_documents',
            'report.billings.credit_notes',
        ]);

        $repartidor->syncPermissions([
            'admin.home',
            'admin.deliveries',
            'admin.jug_movements',
        ]);
    }
}
