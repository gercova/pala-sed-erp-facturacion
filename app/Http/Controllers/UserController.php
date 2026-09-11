<?php

namespace App\Http\Controllers;

use App\Models\ArchingCash;
use App\Models\Billing;
use App\Models\Buy;
use App\Models\Cash;
use App\Models\SaleNote;
use App\Models\TransferOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.list', [
            'roles' => $this->availableRoles(),
            'cashes' => Cash::query()->orderBy('descripcion')->get(),
            'warehouses' => Warehouse::query()->orderBy('descripcion')->get(),
        ]);
    }

    public function get()
    {
        $users = User::query()
            ->with(['cash:id,descripcion', 'activeWarehouse:id,descripcion', 'warehouses:id,descripcion', 'roles:id,name'])
            ->where('id', '!=', 1)
            ->orderByDesc('id');

        return datatables()
            ->of($users)
            ->addColumn('usuario_info', function (User $user) {
                return '<div class="user-name-cell">'
                    . '<div class="fw-semibold">' . e((string) $user->nombres) . '</div>'
                    . '<small class="text-muted">@' . e((string) $user->user) . '</small>'
                    . '</div>';
            })
            ->addColumn('caja', fn (User $user) => e((string) optional($user->cash)->descripcion ?: '-'))
            ->addColumn('rol', function (User $user) {
                $role = optional($user->roles->first())->name;
                return $role
                    ? '<span class="badge bg-light text-dark border">' . e($role) . '</span>'
                    : '<span class="text-muted">Sin rol</span>';
            })
            ->addColumn('almacenes', function (User $user) {
                if ($user->warehouses->isEmpty()) {
                    return '<span class="text-muted">Sin almacenes</span>';
                }

                $activeWarehouseId = (int) ($user->idalmacen ?? 0);

                return $user->warehouses
                    ->map(function (Warehouse $warehouse) use ($activeWarehouseId) {
                        $isActive = (int) $warehouse->id === $activeWarehouseId;

                        return '<span class="badge ' . ($isActive ? 'bg-primary-subtle text-primary' : 'bg-light text-dark border') . ' me-1 mb-1">'
                            . e((string) $warehouse->descripcion)
                            . ($isActive ? ' · activo' : '')
                            . '</span>';
                    })
                    ->implode('');
            })
            ->addColumn('estado_badge', function (User $user) {
                return (int) $user->estado === 1
                    ? '<span class="badge bg-success-subtle text-success">Activo</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">Inactivo</span>';
            })
            ->addColumn('acciones', function (User $user) {
                return '<div class="dropdown">
                            <a href="#" role="button" id="dropdownUser' . (int) $user->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownUser' . (int) $user->id . '">
                                <a class="dropdown-item btn-detail" data-id="' . (int) $user->id . '" href="javascript:void(0);">
                                    <i class="ri-edit-line me-2"></i>
                                    <span> Editar</span>
                                </a>
                                <a class="dropdown-item btn-roles" data-id="' . (int) $user->id . '" href="javascript:void(0);">
                                    <i class="ri-shield-user-line me-2"></i>
                                    <span> Roles rapidos</span>
                                </a>
                                <a class="dropdown-item btn-confirm" data-id="' . (int) $user->id . '" href="javascript:void(0);">
                                    <i class="ri-delete-bin-line me-2"></i>
                                    <span> Eliminar</span>
                                </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['usuario_info', 'rol', 'almacenes', 'estado_badge', 'acciones'])
            ->toJson();
    }

    public function save(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $validator = $this->validateUserRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $warehouseIds = $this->sanitizeWarehouseIds($data['warehouse_ids'] ?? []);
        $primaryWarehouseId = (int) ($warehouseIds[0] ?? 0);

        $user = User::create([
            'nombres' => mb_strtoupper(trim((string) $data['nombres'])),
            'user' => mb_strtolower(trim((string) $data['user'])),
            'password' => trim((string) $data['password']),
            'estado' => (int) $data['estado'],
            'idcaja' => (int) $data['idcaja'],
            'idalmacen' => $primaryWarehouseId,
        ]);

        $user->warehouses()->sync($warehouseIds);
        $user->syncRoles([$data['role']]);

        return response()->json([
            'status' => true,
            'msg' => 'Usuario registrado correctamente.',
            'type' => 'success',
        ]);
    }

    public function detail(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $user = User::query()
            ->with(['roles:id,name', 'warehouses:id,descripcion'])
            ->find((int) $request->input('id'));

        if (! $user) {
            return response()->json(['status' => false, 'msg' => 'El usuario no existe.', 'type' => 'warning'], 404);
        }

        return response()->json([
            'status' => true,
            'user' => $user,
            'role' => optional($user->roles->first())->name,
            'warehouse_ids' => $user->warehouses->pluck('id')->map(fn ($id) => (string) $id)->values(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $user = User::query()->find((int) $request->input('id'));

        if (! $user) {
            return response()->json(['status' => false, 'msg' => 'El usuario no existe.', 'type' => 'warning'], 404);
        }

        $validator = $this->validateUserRequest($request, true, $user);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $warehouseIds = $this->sanitizeWarehouseIds($data['warehouse_ids'] ?? []);
        $primaryWarehouseId = (int) ($warehouseIds[0] ?? 0);

        $payload = [
            'nombres' => mb_strtoupper(trim((string) $data['nombres'])),
            'user' => mb_strtolower(trim((string) $data['user'])),
            'estado' => (int) $data['estado'],
            'idcaja' => (int) $data['idcaja'],
            'idalmacen' => $primaryWarehouseId,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = trim((string) $data['password']);
        }

        $user->update($payload);
        $user->warehouses()->sync($warehouseIds);
        $user->syncRoles([$data['role']]);

        return response()->json([
            'status' => true,
            'msg' => 'Usuario actualizado correctamente.',
            'type' => 'success',
        ]);
    }

    public function delete(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $user = User::query()->find((int) $request->input('id'));

        if (! $user) {
            return response()->json(['status' => false, 'msg' => 'El usuario no existe.', 'type' => 'warning'], 404);
        }

        if ((int) auth()->id() === (int) $user->id) {
            return response()->json([
                'status' => false,
                'msg' => 'No puede eliminar su propio usuario.',
                'type' => 'warning',
            ], 422);
        }

        if ($this->userHasMovements($user->id)) {
            return response()->json([
                'status' => false,
                'msg' => 'El usuario tiene movimientos registrados y no se puede eliminar.',
                'type' => 'warning',
            ], 422);
        }

        $user->warehouses()->detach();
        $user->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
            'type' => 'success',
        ]);
    }

    public function view_role(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $user = User::query()->with('roles:id,name')->find((int) $request->input('id'));

        if (! $user) {
            return response()->json(['status' => false, 'msg' => 'El usuario no existe.', 'type' => 'warning'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
                'roles' => $this->availableRoles()->values(),
                'selRoles' => $user->roles->pluck('name')->values(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $user = User::query()->find((int) $request->input('id'));

        if (! $user) {
            return response()->json(['status' => false, 'msg' => 'El usuario no existe.', 'type' => 'warning'], 404);
        }

        $role = (string) $request->input('roles.0', '');

        if ($role === '' || ! $this->availableRoles()->pluck('name')->contains($role)) {
            return response()->json([
                'status' => false,
                'msg' => 'Debe seleccionar un rol valido.',
                'type' => 'warning',
            ], 422);
        }

        $user->syncRoles([$role]);

        return response()->json([
            'status' => true,
            'msg' => 'Rol asignado correctamente.',
            'type' => 'success',
        ]);
    }

    private function validateUserRequest(Request $request, bool $isUpdate = false, ?User $user = null)
    {
        $rules = [
            'nombres' => ['required', 'string', 'max:255'],
            'user' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'user')->ignore($user?->id),
            ],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6', 'max:255'],
            'idcaja' => ['required', 'integer', 'exists:cashes,id'],
            'warehouse_ids' => ['required', 'array', 'min:1'],
            'warehouse_ids.*' => ['required', 'integer', 'exists:warehouses,id'],
            'role' => ['required', 'string', Rule::in($this->availableRoles()->pluck('name')->all())],
            'estado' => ['required', 'integer', Rule::in([0, 1])],
        ];

        $messages = [
            'warehouse_ids.required' => 'Debe asignar al menos un almacen.',
            'warehouse_ids.min' => 'Debe asignar al menos un almacen.',
            'role.required' => 'Debe seleccionar un rol.',
            'idcaja.required' => 'Debe seleccionar una caja.',
            'password.required' => 'Debe ingresar una contrasena.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function sanitizeWarehouseIds(array $warehouseIds): array
    {
        return collect($warehouseIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function availableRoles()
    {
        return Role::query()
            ->where('name', '!=', 'SUPERADMIN')
            ->orderByRaw("CASE name WHEN 'ADMIN' THEN 1 WHEN 'VENDEDOR' THEN 2 WHEN 'CAJERO' THEN 3 WHEN 'CONTABILIDAD' THEN 4 ELSE 5 END")
            ->get(['id', 'name']);
    }

    private function userHasMovements(int $userId): bool
    {
        return SaleNote::query()->where('idusuario', $userId)->exists()
            || Billing::query()->where('idusuario', $userId)->exists()
            || Buy::query()->where('idusuario', $userId)->exists()
            || TransferOrder::query()->where('idusuario', $userId)->exists()
            || ArchingCash::query()->where('idusuario', $userId)->exists();
    }
}
