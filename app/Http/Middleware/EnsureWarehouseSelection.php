<?php

namespace App\Http\Middleware;

use App\Models\Warehouse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWarehouseSelection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || $request->routeIs('warehouse.selector.*') || $request->routeIs('login.logout')) {
            return $next($request);
        }

        $user = Auth::user();
        $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id')->map(fn ($id) => (int) $id)->filter()->values();

        if ($assignedWarehouseIds->isEmpty() && (int) ($user->idalmacen ?? 0) > 0 && Warehouse::query()->where('id', (int) $user->idalmacen)->exists()) {
            $user->warehouses()->syncWithoutDetaching([(int) $user->idalmacen]);
            $assignedWarehouseIds = collect([(int) $user->idalmacen]);
        }

        if ($assignedWarehouseIds->isEmpty()) {
            return $next($request);
        }

        $selectedWarehouseId = (int) $request->session()->get('selected_warehouse_id', (int) ($user->idalmacen ?? 0));

        if ($assignedWarehouseIds->count() === 1) {
            $selectedWarehouseId = (int) $assignedWarehouseIds->first();
        }

        if (! $assignedWarehouseIds->contains($selectedWarehouseId)) {
            return redirect()->route('warehouse.selector.index', [
                'redirect_to' => $request->fullUrl(),
            ]);
        }

        $request->session()->put('selected_warehouse_id', $selectedWarehouseId);

        if ((int) $user->idalmacen !== $selectedWarehouseId) {
            $user->forceFill(['idalmacen' => $selectedWarehouseId])->saveQuietly();
            $user->idalmacen = $selectedWarehouseId;
        }

        return $next($request);
    }
}
