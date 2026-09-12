<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Maximum failed login attempts before throttling kicks in.
     */
    private const MAX_ATTEMPTS = 5;

    public function index(): View
    {
        $data['logo'] = Business::first()->logo;
        return view('login', $data);
    }

    public function login(Request $request): RedirectResponse
    {
        // ── 1. Basic input validation ────────────────────────────────────────
        $request->validate([
            'user'     => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'user.required'     => 'El campo usuario es obligatorio.',
            'password.required' => 'El campo contraseña es obligatorio.',
        ]);

        $username = strtolower(trim((string) $request->input('user')));
        $password = trim((string) $request->input('password'));

        // ── 2. Rate-limit check ──────────────────────────────────────────────
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Log::warning('Login bloqueado por demasiados intentos.', [
                'username' => $username,
                'ip'       => $request->ip(),
                'retry_in' => $seconds,
            ]);

            return back()->with(
                'message',
                "Demasiados intentos fallidos. Intente de nuevo en {$seconds} segundos."
            );
        }

        // ── 3. Attempt authentication ────────────────────────────────────────
        $credentials = [
            'user'     => $username,
            'password' => $password,
        ];

        if (! Auth::attempt($credentials, false)) {
            // Increment the counter and log the failure.
            RateLimiter::hit($throttleKey, 60); // decay: 60 seconds

            Log::warning('Intento de inicio de sesión fallido.', [
                'username' => $username,
                'ip'       => $request->ip(),
                'attempts' => RateLimiter::attempts($throttleKey),
            ]);

            Auth::logout();

            // Generic message to avoid user-enumeration.
            return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
        }

        // ── 4. Check user status ─────────────────────────────────────────────
        /** @var User $authUser */
        $authUser = User::query()->find(Auth::user()->id);

        if ((int) ($authUser->estado ?? 0) !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::warning('Intento de acceso de usuario inactivo.', [
                'username' => $username,
                'ip'       => $request->ip(),
            ]);

            return back()->with('message', 'No tiene permisos para acceder al sistema. Contacte al administrador.');
        }

        // ── 5. Successful auth: session fixation + clear rate limiter ────────
        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);

        Log::info('Inicio de sesión exitoso.', [
            'user_id'  => $authUser->id,
            'username' => $username,
            'ip'       => $request->ip(),
        ]);

        // ── 6. Warehouse resolution ──────────────────────────────────────────
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build the unique throttle key: username + IP (lowercased & normalized).
     */
    private function throttleKey(Request $request): string
    {
        return Str::lower(
            strtolower(trim((string) $request->input('user', '')))
            . '|'
            . $request->ip()
        );
    }
}
