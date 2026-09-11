<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.list', [
            'permissions' => $permissions,
            'permissionGroups' => $this->groupedPermissions($permissions),
        ]);
    }

    public function get()
    {
        $roles = Role::query()
            ->where('name', '!=', 'SUPERADMIN')
            ->orderBy('id', 'DESC');

        return datatables()
            ->of($roles)
            ->addColumn('acciones', function ($roles) {
                $id = $roles->id;
                $btn = '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1" style="">
                                    <a class="dropdown-item btn-detail" data-id="' . $id . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit menu-icon"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span> Actualizar</span>
                                </a>
                                    <a class="dropdown-item btn-confirm" data-id="' . $id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 menu-icon"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    <span> Eliminar</span>
                            </a>
                            </div>
                            </div>';

                return $btn;
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function save(Request $request)
    {
        if (! $request->ajax()) {
            echo json_encode([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'title' => 'Espere',
                'type' => 'warning'
            ]);
            return;
        }

        $role = Role::create(['name' => mb_strtoupper($request->name)]);
        $role->permissions()->sync($request->permissions);

        echo json_encode([
            'status' => true,
            'msg' => 'Datos guardados correctamente',
            'type' => 'success'
        ]);
    }

    public function detail(Request $request)
    {
        if (! $request->ajax()) {
            echo json_encode([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'title' => 'Espere',
                'type' => 'warning'
            ]);
            return;
        }

        $role = Role::where('id', $request->input('id'))->first();
        $permissions = Permission::orderBy('name')->get();

        echo json_encode([
            'status' => true,
            'data' => [
                'role' => $role,
                'permissions' => $permissions,
                'permissionGroups' => $this->groupedPermissions($permissions),
                'selPermissions' => $role->permissions->pluck('id')->toArray(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->ajax()) {
            echo json_encode([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
            return;
        }

        $role = Role::where('id', $request->input('id'))->first();
        $role->update($request->all());
        $role->permissions()->sync($request->permissions);

        echo json_encode([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success'
        ]);
    }

    public function delete(Request $request)
    {
        if (! $request->ajax()) {
            echo json_encode([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
            return;
        }

        Role::where('id', $request->input('id'))->delete();

        echo json_encode([
            'status' => true,
            'msg' => 'Registro eliminado con exito',
            'type' => 'success'
        ]);
    }

    private function groupedPermissions($permissions)
    {
        $metadata = [
            'Dashboard' => 'Panel principal e indicadores generales.',
            'Ventas' => 'Clientes, cotizaciones, POS, notas de venta, comprobantes y guias.',
            'Compras' => 'Proveedores y flujo de compras.',
            'Inventario' => 'Catalogo, almacenes y traslados.',
            'Operaciones' => 'Arqueo de caja y operacion diaria.',
            'Configuracion' => 'Empresa, cajas, series, usuarios y roles.',
            'Reportes' => 'Consultas y reportes operativos.',
            'Otros' => 'Permisos adicionales del sistema.',
        ];

        return $permissions
            ->groupBy(function ($permission) {
                $name = (string) $permission->name;

                return match (true) {
                    $name === 'admin.home' => 'Dashboard',
                    in_array($name, ['admin.clients', 'admin.quotes', 'admin.pos', 'admin.sale_notes', 'admin.billings', 'admin.shipment_guides'], true) => 'Ventas',
                    in_array($name, ['admin.providers', 'admin.buys'], true) => 'Compras',
                    in_array($name, ['admin.products', 'admin.categories', 'admin.warehouses', 'admin.transfer_orders'], true) => 'Inventario',
                    in_array($name, ['admin.arching_cashes'], true) => 'Operaciones',
                    in_array($name, ['admin.business', 'admin.paymodes', 'admin.cashes', 'admin.series', 'admin.users', 'admin.roles'], true) => 'Configuracion',
                    str_starts_with($name, 'report.') => 'Reportes',
                    default => 'Otros',
                };
            })
            ->map(function ($items, $label) use ($metadata) {
                return [
                    'label' => $label,
                    'description' => $metadata[$label] ?? '',
                    'permissions' => $items->values(),
                ];
            })
            ->values();
    }
}
