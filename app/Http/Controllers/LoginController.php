<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\IdentityDocumentType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    /** Ubigeos permitidos (Tarapoto, Morales, La Banda de Shilcayo, Shapaja) */
    private const ALLOWED_UBIGEOS = ['220601', '220602', '220603', '220609'];

    /** Nombres de los distritos para mensajes de error */
    private const ALLOWED_DISTRICTS = [
        '220601' => 'Tarapoto',
        '220602' => 'Morales',
        '220603' => 'La Banda de Shilcayo',
        '220609' => 'Shapaja',
    ];

    public function index(): View
    {
        $business  = Business::first();
        $logo      = $business?->logo;
        $docTypes  = IdentityDocumentType::where('estado', 1)->get();

        return view('login', compact('logo', 'docTypes'));
    }

    // ── Autenticación ──────────────────────────────────────────────────────────

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'user'     => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'user.required'     => 'El campo usuario es obligatorio.',
            'password.required' => 'El campo contraseña es obligatorio.',
        ]);

        $username    = strtolower(trim((string) $request->input('user')));
        $password    = trim((string) $request->input('password'));
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning('Login bloqueado por demasiados intentos.', ['username' => $username, 'ip' => $request->ip()]);

            return back()->with('message', "Demasiados intentos fallidos. Intente de nuevo en {$seconds} segundos.");
        }

        if (! Auth::attempt(['user' => $username, 'password' => $password], false)) {
            RateLimiter::hit($throttleKey, 60);
            Log::warning('Intento de login fallido.', ['username' => $username, 'ip' => $request->ip()]);
            Auth::logout();

            return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
        }

        /** @var User $authUser */
        $authUser = User::query()->find(Auth::user()->id);

        if ((int) ($authUser->estado ?? 0) !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('message', 'No tiene permisos para acceder al sistema. Contacte al administrador.');
        }

        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);
        Log::info('Login exitoso.', ['user_id' => $authUser->id, 'ip' => $request->ip()]);

        // ── Redirigir según rol ──────────────────────────────────────────────
        if ($authUser->hasRole('Cliente')) {
            return redirect()->route('cliente.dashboard')->with('message_welcome', 'Bienvenido a tu portal.');
        }

        // ── Resolución de almacén para usuarios admin ────────────────────────
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

    // ── Registro de clientes ───────────────────────────────────────────────────

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'reg_iddoc'        => ['required', 'integer', 'exists:identity_document_types,id'],
            'reg_nro_doc'      => ['required', 'string', 'max:20'],
            'reg_nombres'      => ['required', 'string', 'max:255'],
            'reg_telefono'     => ['required', 'string', 'max:15', 'regex:/^[0-9+\s\-]+$/'],
            'reg_ubigeo'       => ['required', 'string', 'in:' . implode(',', self::ALLOWED_UBIGEOS)],
            'reg_direccion'    => ['required', 'string', 'max:255'],
            'reg_password'     => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ], [
            'reg_ubigeo.in'        => 'Solo atendemos en: ' . implode(', ', self::ALLOWED_DISTRICTS) . '.',
            'reg_nro_doc.required' => 'El número de documento es obligatorio.',
            'reg_password.confirmed' => 'Las contraseñas no coinciden.',
            'reg_password.min'     => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // Verificar unicidad del documento
        $docExists = Client::where('nro_documento', $request->input('reg_nro_doc'))->exists();
        if ($docExists) {
            return back()
                ->withInput()
                ->withErrors(['reg_nro_doc' => 'Ya existe un cliente registrado con ese documento.'])
                ->with('active_tab', 'register');
        }

        // Verificar unicidad del teléfono
        $phoneExists = Client::where('telefono', $request->input('reg_telefono'))->exists();
        if ($phoneExists) {
            return back()
                ->withInput()
                ->withErrors(['reg_telefono' => 'Ya existe un cliente registrado con ese teléfono.'])
                ->with('active_tab', 'register');
        }

        // Verificar que no exista el usuario
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('reg_nro_doc')));
        if (User::where('user', $username)->exists()) {
            $username = $username . rand(10, 99);
        }

        return DB::transaction(function () use ($request, $username) {
            // 1. Crear cliente
            $client = Client::create([
                'iddoc'        => $request->input('reg_iddoc'),
                'nro_documento' => trim($request->input('reg_nro_doc')),
                'nombres'      => mb_strtoupper(trim($request->input('reg_nombres'))),
                'telefono'     => trim($request->input('reg_telefono')),
                'ubigeo'       => $request->input('reg_ubigeo'),
                'direccion'    => mb_strtoupper(trim($request->input('reg_direccion'))),
                'codigo_pais'  => 'PE',
                'saldo_envases' => 0,
            ]);

            // 2. Crear usuario vinculado
            $user = User::create([
                'nombres'    => mb_strtoupper(trim($request->input('reg_nombres'))),
                'user'       => $username,
                'password'   => Hash::make($request->input('reg_password')),
                'estado'     => 1,
                'idcaja'     => null,
                'idalmacen'  => null,
                'idcliente'  => $client->id,
                'tipo'       => 'cliente',
            ]);

            // 3. Asignar rol
            $user->assignRole('Cliente');

            // 4. Login automático
            Auth::login($user);
            $request->session()->regenerate();

            Log::info('Nuevo cliente registrado.', ['user_id' => $user->id, 'client_id' => $client->id]);

            return redirect()->route('cliente.dashboard')
                ->with('message_welcome', '¡Registro exitoso! Bienvenido/a, ' . ucwords(strtolower($client->nombres)) . '.');
        });
    }

    // ── Logout ─────────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function throttleKey(Request $request): string
    {
        return Str::lower(
            strtolower(trim((string) $request->input('user', '')))
            . '|' . $request->ip()
        );
    }
}
