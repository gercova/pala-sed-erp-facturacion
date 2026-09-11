<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        $data['logo'] = Business::first()->logo;

        return view('login', $data);
    }

    public function login(Request $request)
    {
        $user = trim((string) $request->input('user'));
        $password = trim((string) $request->input('password'));

        if ($user === '' || $password === '') {
            return back()->with('message', 'El campo usuario es obligatorio.');
        }

        $credentials = [
            'user' => strtolower($user),
            'password' => $password,
        ];

        if (! Auth::attempt($credentials)) {
            Auth::logout();

            return back()->with('message', 'Estas credenciales no coinciden con nuestros registros.');
        }

        $authUser = User::query()->find(Auth::user()->id);

        if ((int) ($authUser->estado ?? 0) !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('message', 'No tiene los permisos necesarios');
        }

        $request->session()->regenerate();

        $warehouseIds = method_exists($authUser, 'warehouses')
            ? $authUser->warehouses()->pluck('warehouses.id')->map(fn ($id) => (int) $id)->filter()->values()
            : collect();

        if ($warehouseIds->isEmpty() && (int) ($authUser->idalmacen ?? 0) > 0) {
            $request->session()->put('selected_warehouse_id', (int) $authUser->idalmacen);
        } elseif ($warehouseIds->count() === 1) {
            $selectedWarehouseId = (int) $warehouseIds->first();
            $request->session()->put('selected_warehouse_id', $selectedWarehouseId);

            if ((int) $authUser->idalmacen !== $selectedWarehouseId) {
                $authUser->forceFill(['idalmacen' => $selectedWarehouseId])->saveQuietly();
            }
        } elseif ($warehouseIds->count() > 1) {
            $request->session()->forget('selected_warehouse_id');

            return redirect()->route('warehouse.selector.index');
        }

        return redirect()->route('admin.home')->with('message_welcome', 'Bienvenido al sistema.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
