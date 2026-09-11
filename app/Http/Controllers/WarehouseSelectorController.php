<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WarehouseSelectorController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouses = $user->warehouses()->orderBy('descripcion')->get();

        if ($warehouses->count() <= 1) {
            return redirect()->route('admin.home');
        }

        return view('establishment', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => (int) session('selected_warehouse_id', $user->idalmacen),
            'redirectTo' => (string) $request->query('redirect_to', route('admin.home')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $warehouseId = (int) $validated['warehouse_id'];

        $hasAccess = $user->warehouses()
            ->where('warehouses.id', $warehouseId)
            ->exists();

        if (! $hasAccess) {
            return back()->with('message', 'No tiene acceso al almacen seleccionado.');
        }

        session(['selected_warehouse_id' => $warehouseId]);
        $user->forceFill(['idalmacen' => $warehouseId])->saveQuietly();

        $redirectTo = (string) ($validated['redirect_to'] ?? route('admin.home'));

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'msg' => 'Almacen activo actualizado correctamente.',
                'type' => 'success',
                'redirect_to' => $this->resolveRedirectUrl($redirectTo, $warehouseId),
            ]);
        }

        return redirect()->to($this->resolveRedirectUrl($redirectTo, $warehouseId));
    }

    private function resolveRedirectUrl(string $redirectTo, int $warehouseId): string
    {
        if ($redirectTo === '' || ! str_starts_with($redirectTo, url('/'))) {
            return route('admin.home');
        }

        $parts = parse_url($redirectTo);
        $path = (string) ($parts['path'] ?? '/');
        $updatedPath = preg_replace('/\/warehouses\/\d+(?=\/|$)/', '/warehouses/' . $warehouseId, $path, 1) ?: $path;
        $resolvedUrl = url($updatedPath);

        if (! empty($parts['query'])) {
            $resolvedUrl .= '?' . $parts['query'];
        }

        if (! empty($parts['fragment'])) {
            $resolvedUrl .= '#' . $parts['fragment'];
        }

        return $resolvedUrl;
    }
}
