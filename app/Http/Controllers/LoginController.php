<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\IdentityDocumentType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_USER_ATTEMPTS = 5;

    private const MAX_IP_ATTEMPTS = 10;

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
        $business = Business::first();
        $logo = $business?->logo;
        $docTypes = IdentityDocumentType::where('estado', 1)->get();
        $clientAuthMethod = Business::getClientAuthMethod();

        return view('login', compact('logo', 'docTypes', 'clientAuthMethod'));
    }

    // ── Autenticación ──────────────────────────────────────────────────────────

    public function login(Request $request): RedirectResponse
    {
        $clientAuthMethod = Business::getClientAuthMethod();

        $username = strtolower(trim((string) $request->input('user', '')));
        $password = (string) $request->input('password', '');
        $otpCode = trim((string) $request->input('otp_code', ''));

        // Dual rate limiting: por IP y por DNI/Usuario
        $ipThrottleKey = 'login_ip:'.$request->ip();
        $userThrottleKey = 'login_user:'.Str::lower($username);

        if (RateLimiter::tooManyAttempts($ipThrottleKey, self::MAX_IP_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            Log::warning('Login bloqueado por IP.', ['ip' => $request->ip()]);

            return back()->with('message', "Demasiados intentos fallidos. Intente de nuevo en {$seconds} segundos.");
        }

        if (! empty($username) && RateLimiter::tooManyAttempts($userThrottleKey, self::MAX_USER_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($userThrottleKey);
            Log::warning('Login bloqueado por usuario/DNI.', ['username' => $username, 'ip' => $request->ip()]);

            return back()->with('message', "Demasiados intentos fallidos. Intente de nuevo en {$seconds} segundos.");
        }

        // Buscar usuario por username o por DNI asociado al perfil de cliente
        $targetUser = null;
        if (! empty($username)) {
            $targetUser = User::where('user', $username)
                ->orWhereHas('clientProfile', fn ($q) => $q->where('nro_documento', $username))
                ->first();
        }

        // ── CASO OTP: Validación de código si fue enviado ──────────────────────
        if ($clientAuthMethod === 'otp' && ! empty($otpCode)) {
            $pendingUserId = $request->input('otp_user_id') ?? $request->session()->get('pending_otp_user_id');
            $cachedHash = $pendingUserId ? Cache::get('client_otp_'.$pendingUserId) : null;

            if ($cachedHash && Hash::check($otpCode, $cachedHash)) {
                Cache::forget('client_otp_'.$pendingUserId);
                $request->session()->forget('pending_otp_user_id');

                /** @var User|null $authUser */
                $authUser = User::find($pendingUserId);
                if ($authUser && $authUser->hasRole('Cliente') && (int) ($authUser->estado ?? 0) === 1) {
                    Auth::login($authUser);
                    $request->session()->regenerate();
                    RateLimiter::clear($ipThrottleKey);
                    RateLimiter::clear('login_user:'.Str::lower($authUser->user));
                    Log::info('Login de cliente por OTP exitoso.', ['user_id' => $authUser->id, 'ip' => $request->ip()]);

                    return redirect()->route('cliente.dashboard')->with('message_welcome', 'Bienvenido a tu portal.');
                }
            }

            RateLimiter::hit($ipThrottleKey, 60);
            if (! empty($username)) {
                RateLimiter::hit($userThrottleKey, 60);
            }

            return back()->with('message', 'Código de verificación inválido o expirado.')->with('otp_step', true)->with('otp_user_id', $pendingUserId);
        }

        // Si el usuario no ingresó usuario/DNI
        if (empty($username)) {
            return back()->with('message', 'El campo usuario o DNI es obligatorio.');
        }

        // ── CASO DNI: Acceso con DNI para clientes ─────────────────────────────
        if ($clientAuthMethod === 'dni' && $password === '') {
            // REGLA INVARIANTE: NUNCA debilitar operadores internos (SuperAdmin, Admin, Cajero, Repartidor)
            if ($targetUser && $targetUser->hasRole('Cliente')) {
                if ((int) ($targetUser->estado ?? 0) !== 1) {
                    RateLimiter::hit($ipThrottleKey, 60);
                    RateLimiter::hit($userThrottleKey, 60);

                    return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
                }

                Auth::login($targetUser);
                $request->session()->regenerate();
                RateLimiter::clear($ipThrottleKey);
                RateLimiter::clear($userThrottleKey);
                Log::info('Login de cliente por DNI (modo dni).', ['user_id' => $targetUser->id, 'ip' => $request->ip()]);

                return redirect()->route('cliente.dashboard')->with('message_welcome', 'Bienvenido a tu portal.');
            }

            // Si es un operador interno o no existe, RECHAZAR con mensaje genérico
            RateLimiter::hit($ipThrottleKey, 60);
            RateLimiter::hit($userThrottleKey, 60);

            return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
        }

        // ── CASO OTP (Paso 1: Solicitud de código OTP) ─────────────────────────
        if ($clientAuthMethod === 'otp' && empty($otpCode) && $password === '') {
            if ($targetUser && $targetUser->hasRole('Cliente')) {
                if ((int) ($targetUser->estado ?? 0) !== 1) {
                    RateLimiter::hit($ipThrottleKey, 60);
                    RateLimiter::hit($userThrottleKey, 60);

                    return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
                }

                $otp = (string) random_int(100000, 999999);
                Cache::put('client_otp_'.$targetUser->id, Hash::make($otp), now()->addMinutes(5));
                $request->session()->put('pending_otp_user_id', $targetUser->id);

                Log::info("OTP generado para cliente {$targetUser->user}: {$otp}");

                $phone = $targetUser->clientProfile?->telefono;
                $cleanPhone = $phone ? preg_replace('/[^0-9]/', '', $phone) : null;
                $waUrl = $cleanPhone ? 'https://wa.me/51'.$cleanPhone.'?text='.urlencode("Tu código de acceso a Pala-Sed es: {$otp}") : null;

                return back()
                    ->with('otp_step', true)
                    ->with('otp_user_id', $targetUser->id)
                    ->with('otp_wa_url', $waUrl)
                    ->with('message_info', 'Hemos generado un código de verificación de 6 dígitos.');
            }

            // Si es operador interno o no existe, rechazar con mensaje genérico
            RateLimiter::hit($ipThrottleKey, 60);
            RateLimiter::hit($userThrottleKey, 60);

            return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
        }

        // ── AUTENTICACIÓN TRADICIONAL CON CONTRASEÑA ───────────────────────────
        // (Aplica a clientes en modo 'password', o a operadores internos en cualquier modo)
        $userToAttempt = $targetUser ?? User::where('user', $username)->first();

        if (! $userToAttempt || ! Hash::check($password, $userToAttempt->password)) {
            RateLimiter::hit($ipThrottleKey, 60);
            RateLimiter::hit($userThrottleKey, 60);
            Log::warning('Intento de login fallido.', ['username' => $username, 'ip' => $request->ip()]);

            return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
        }

        if ((int) ($userToAttempt->estado ?? 0) !== 1) {
            RateLimiter::hit($ipThrottleKey, 60);
            RateLimiter::hit($userThrottleKey, 60);

            return back()->with('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');
        }

        Auth::login($userToAttempt);
        $request->session()->regenerate();
        RateLimiter::clear($ipThrottleKey);
        RateLimiter::clear($userThrottleKey);
        Log::info('Login exitoso.', ['user_id' => $userToAttempt->id, 'ip' => $request->ip()]);

        if ($userToAttempt->hasRole('Cliente')) {
            return redirect()->route('cliente.dashboard')->with('message_welcome', 'Bienvenido a tu portal.');
        }

        // Resolución de almacén para usuarios internos/admin
        $warehouseIds = method_exists($userToAttempt, 'warehouses')
            ? $userToAttempt->warehouses()->pluck('warehouses.id')->map(fn ($id) => (int) $id)->filter()->values()
            : collect();

        if ($warehouseIds->isEmpty() && (int) ($userToAttempt->idalmacen ?? 0) > 0) {
            $request->session()->put('selected_warehouse_id', (int) $userToAttempt->idalmacen);
        } elseif ($warehouseIds->count() === 1) {
            $selectedWarehouseId = (int) $warehouseIds->first();
            $request->session()->put('selected_warehouse_id', $selectedWarehouseId);

            if ((int) $userToAttempt->idalmacen !== $selectedWarehouseId) {
                $userToAttempt->forceFill(['idalmacen' => $selectedWarehouseId])->saveQuietly();
            }
        } elseif ($warehouseIds->count() > 1) {
            $request->session()->forget('selected_warehouse_id');

            return redirect()->route('warehouse.selector.index');
        }

        return redirect()->route('admin.home')->with('message_welcome', 'Bienvenido al sistema.');
    }

    // ── Consulta RENIEC de DNI para Autocompletado ─────────────────────────────

    public function consultDni(Request $request): JsonResponse
    {
        $dni = trim((string) $request->input('dni', ''));

        if (! preg_match('/^\d{8}$/', $dni)) {
            return response()->json([
                'status' => false,
                'msg' => 'Para DNI debe ingresar exactamente 8 dígitos numéricos.',
            ], 422);
        }

        $ipThrottleKey = 'reniec_ip:'.$request->ip();
        $dniThrottleKey = 'reniec_dni:'.$dni;

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 15)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);

            return response()->json([
                'status' => false,
                'msg' => "Demasiadas consultas. Intente nuevamente en {$seconds} segundos.",
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($dniThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($dniThrottleKey);

            return response()->json([
                'status' => false,
                'msg' => "Demasiadas consultas. Intente nuevamente en {$seconds} segundos.",
            ], 429);
        }

        RateLimiter::hit($ipThrottleKey, 60);
        RateLimiter::hit($dniThrottleKey, 300);

        $document = $this->verify__client($dni);

        if (! isset($document->status) || (int) $document->status !== 200) {
            return response()->json([
                'status' => false,
                'msg' => 'No se pudo consultar el documento. Ingrese su nombre manualmente.',
                'found' => false,
            ], 200);
        }

        $data = $document->data ?? null;
        if (! $data) {
            return response()->json([
                'status' => false,
                'msg' => 'No se encontró información para el documento consultado.',
                'found' => false,
            ], 200);
        }

        $names = trim(($data->nombres ?? '').' '.($data->apellido_paterno ?? '').' '.($data->apellido_materno ?? ''));

        return response()->json([
            'status' => true,
            'found' => true,
            'nombres' => $names,
        ]);
    }

    // ── Registro Inicial de Clientes ──────────────────────────────────────────

    public function register(Request $request): RedirectResponse
    {
        $clientAuthMethod = Business::getClientAuthMethod();

        $rules = [
            'reg_nro_doc' => ['required', 'string', 'regex:/^\d{8}$/'],
            'reg_nombres' => ['required', 'string', 'max:255'],
            'reg_telefono' => ['required', 'string', 'max:15', 'regex:/^[0-9+\s\-]+$/'],
            'reg_direccion' => ['required', 'string', 'max:255'],
            'reg_referencia' => ['nullable', 'string', 'max:255'],
            'reg_coordenadas' => ['nullable', 'string', 'max:100'],
            'reg_ubigeo' => ['nullable', 'string', 'in:'.implode(',', self::ALLOWED_UBIGEOS)],
        ];

        if ($clientAuthMethod === 'password') {
            $rules['reg_password'] = ['required', 'string', 'min:8', 'max:255', 'confirmed'];
        } else {
            $rules['reg_password'] = ['nullable', 'string', 'min:8', 'max:255'];
        }

        $request->validate($rules, [
            'reg_nro_doc.required' => 'El número de DNI es obligatorio.',
            'reg_nro_doc.regex' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            'reg_nombres.required' => 'El nombre completo es obligatorio.',
            'reg_telefono.required' => 'El número de teléfono es obligatorio.',
            'reg_direccion.required' => 'La dirección es obligatoria.',
            'reg_ubigeo.in' => 'Solo atendemos en: '.implode(', ', self::ALLOWED_DISTRICTS).'.',
            'reg_password.required' => 'La contraseña es obligatoria.',
            'reg_password.confirmed' => 'Las contraseñas no coinciden.',
            'reg_password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $dni = trim((string) $request->input('reg_nro_doc'));

        // Mensaje genérico para evitar enumeración de documentos ya registrados
        $docExists = Client::where('nro_documento', $dni)->exists();
        $userExists = User::where('user', $dni)->exists();
        if ($docExists || $userExists) {
            return back()
                ->withInput()
                ->withErrors(['reg_nro_doc' => 'No es posible registrar este documento. Si ya tiene cuenta, inicie sesión.'])
                ->with('active_tab', 'register');
        }

        return DB::transaction(function () use ($request, $dni) {
            $client = Client::create([
                'iddoc' => 1,
                'nro_documento' => $dni,
                'nombres' => mb_strtoupper(trim((string) $request->input('reg_nombres'))),
                'telefono' => trim((string) $request->input('reg_telefono')),
                'ubigeo' => $request->input('reg_ubigeo') ?: '220601',
                'direccion' => mb_strtoupper(trim((string) $request->input('reg_direccion'))),
                'referencia' => trim((string) $request->input('reg_referencia')),
                'coordenadas' => trim((string) $request->input('reg_coordenadas')),
                'codigo_pais' => 'PE',
                'saldo_envases' => 0,
            ]);

            $rawPassword = $request->input('reg_password');
            $password = (! empty($rawPassword))
                ? Hash::make($rawPassword)
                : Hash::make(Str::random(32));

            $user = User::create([
                'nombres' => mb_strtoupper(trim((string) $request->input('reg_nombres'))),
                'user' => $dni,
                'password' => $password,
                'estado' => 1,
                'idcaja' => null,
                'idalmacen' => null,
                'idcliente' => $client->id,
                'tipo' => 'cliente',
            ]);

            $user->assignRole('Cliente');

            Auth::login($user);
            $request->session()->regenerate();

            Log::info('Nuevo cliente registrado.', ['user_id' => $user->id, 'client_id' => $client->id]);

            return redirect()->route('cliente.dashboard')
                ->with('message_welcome', '¡Registro exitoso! Bienvenido/a, '.ucwords(strtolower($client->nombres)).'.');
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
}
