<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Client;
use App\Models\IdentityDocumentType;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\ClienteRoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientSecurityAndAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected Business $business;

    protected Role $clienteRole;

    protected Role $adminRole;

    protected int $docId;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login_ip:127.0.0.1');
        RateLimiter::clear('reniec_ip:127.0.0.1');
        RateLimiter::clear('qr_check_ip:127.0.0.1');

        $this->seed(ClienteRoleSeeder::class);

        $this->clienteRole = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        $this->adminRole = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);

        $this->business = Business::firstOrCreate(['id' => 1], [
            'ruc' => '20123456789',
            'razon_social' => 'AGUA PURIFICADA TEST SAC',
            'nombre_comercial' => 'PALA-SED',
            'telefono' => '942123456',
            'auth_cliente_metodo' => 'password',
        ]);

        $doc = IdentityDocumentType::firstOrCreate(['codigo' => '1'], [
            'descripcion' => 'DNI',
            'descripcion_documento' => 'DNI',
            'estado' => 1,
        ]);
        $this->docId = (int) $doc->id;
    }

    /**
     * Requirement: Complete initial registration with 8-digit DNI, phone, address, reference, coordinates.
     */
    public function test_complete_initial_registration_with_8_digit_dni_and_location(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'password']);

        $response = $this->post(route('login.register'), [
            'reg_nro_doc' => '45678901',
            'reg_nombres' => 'MARIA GARCIA PEREZ',
            'reg_telefono' => '942111222',
            'reg_ubigeo' => '220601',
            'reg_direccion' => 'Jr. San Martin 450',
            'reg_referencia' => 'Frente al parque infantil',
            'reg_coordenadas' => '-6.485200,-76.368200',
            'reg_password' => 'secret1234',
            'reg_password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('cliente.dashboard'));

        $this->assertDatabaseHas('clients', [
            'nro_documento' => '45678901',
            'nombres' => 'MARIA GARCIA PEREZ',
            'telefono' => '942111222',
            'direccion' => 'JR. SAN MARTIN 450',
            'referencia' => 'Frente al parque infantil',
            'coordenadas' => '-6.485200,-76.368200',
        ]);

        $this->assertDatabaseHas('users', [
            'user' => '45678901',
            'tipo' => 'cliente',
        ]);

        $createdUser = User::where('user', '45678901')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Cliente'));
        $this->assertEquals(Auth::id(), $createdUser->id);
    }

    /**
     * Requirement: DNI 8-digit validation failure.
     */
    public function test_registration_fails_if_dni_is_not_8_digits(): void
    {
        // 7 digits
        $response7 = $this->from(route('login'))->post(route('login.register'), [
            'reg_nro_doc' => '1234567',
            'reg_nombres' => 'JUAN INVALIDO',
            'reg_telefono' => '942111222',
            'reg_direccion' => 'Jr. Lima 123',
            'reg_password' => 'secret1234',
            'reg_password_confirmation' => 'secret1234',
        ]);
        $response7->assertSessionHasErrors(['reg_nro_doc']);

        // 9 digits
        $response9 = $this->from(route('login'))->post(route('login.register'), [
            'reg_nro_doc' => '123456789',
            'reg_nombres' => 'JUAN INVALIDO',
            'reg_telefono' => '942111222',
            'reg_direccion' => 'Jr. Lima 123',
            'reg_password' => 'secret1234',
            'reg_password_confirmation' => 'secret1234',
        ]);
        $response9->assertSessionHasErrors(['reg_nro_doc']);

        // Alpha characters
        $responseAlpha = $this->from(route('login'))->post(route('login.register'), [
            'reg_nro_doc' => '1234ABCD',
            'reg_nombres' => 'JUAN INVALIDO',
            'reg_telefono' => '942111222',
            'reg_direccion' => 'Jr. Lima 123',
            'reg_password' => 'secret1234',
            'reg_password_confirmation' => 'secret1234',
        ]);
        $responseAlpha->assertSessionHasErrors(['reg_nro_doc']);
    }

    /**
     * Requirement: Client login with configured 'password' mode.
     */
    public function test_client_login_with_password_mode(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'password']);

        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '71234567',
            'nombres' => 'CLIENTE PASSWORD TEST',
            'telefono' => '942999888',
            'direccion' => 'Av. Test 100',
            'saldo_envases' => 0,
        ]);

        $user = User::create([
            'nombres' => 'CLIENTE PASSWORD TEST',
            'user' => '71234567',
            'password' => Hash::make('ClaveSegura123'),
            'estado' => 1,
            'idcliente' => $client->id,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $response = $this->post(route('login.login'), [
            'user' => '71234567',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertRedirect(route('cliente.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Requirement: Client login with configured 'dni' mode allows passwordless for Cliente role.
     */
    public function test_client_login_with_dni_mode_allows_client_without_password(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'dni']);

        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '72223334',
            'nombres' => 'CLIENTE DNI FAST TEST',
            'telefono' => '942777666',
            'direccion' => 'Jr. Tarapoto 200',
            'saldo_envases' => 0,
        ]);

        $user = User::create([
            'nombres' => 'CLIENTE DNI FAST TEST',
            'user' => '72223334',
            'password' => Hash::make('RandomPassIgnored'),
            'estado' => 1,
            'idcliente' => $client->id,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $response = $this->post(route('login.login'), [
            'user' => '72223334',
            'password' => '', // No password required in 'dni' mode for clients
        ]);

        $response->assertRedirect(route('cliente.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Invariant: In 'dni' mode, internal operators MUST NEVER be allowed to log in without password!
     */
    public function test_dni_mode_does_not_weaken_internal_operators_login(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'dni']);

        $adminUser = User::create([
            'nombres' => 'SUPER ADMINISTRADOR',
            'user' => 'admin_central',
            'password' => Hash::make('AdminPass123'),
            'estado' => 1,
            'tipo' => 'admin',
        ]);
        $adminUser->assignRole('ADMIN');

        // Attempt login without password -> MUST FAIL!
        $failResponse = $this->from(route('login'))->post(route('login.login'), [
            'user' => 'admin_central',
            'password' => '',
        ]);

        $failResponse->assertRedirect(route('login'));
        $this->assertGuest();
        $failResponse->assertSessionHas('message', 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.');

        // Attempt login with valid password -> MUST SUCCEED!
        $successResponse = $this->post(route('login.login'), [
            'user' => 'admin_central',
            'password' => 'AdminPass123',
        ]);

        $this->assertAuthenticatedAs($adminUser);
    }

    /**
     * Requirement: Client login with configured 'otp' mode.
     */
    public function test_client_login_with_otp_mode_generates_and_validates_otp(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'otp']);

        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '73334445',
            'nombres' => 'CLIENTE OTP TEST',
            'telefono' => '942555444',
            'direccion' => 'Jr. Morales 500',
            'saldo_envases' => 0,
        ]);

        $user = User::create([
            'nombres' => 'CLIENTE OTP TEST',
            'user' => '73334445',
            'password' => Hash::make('PassIgnoredInOtp'),
            'estado' => 1,
            'idcliente' => $client->id,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        // Step 1: Request OTP by submitting DNI
        $step1Response = $this->from(route('login'))->post(route('login.login'), [
            'user' => '73334445',
            'password' => '',
        ]);

        $step1Response->assertRedirect(route('login'));
        $step1Response->assertSessionHas('otp_step', true);
        $step1Response->assertSessionHas('otp_user_id', $user->id);

        $cachedOtpHash = Cache::get('client_otp_'.$user->id);
        $this->assertNotNull($cachedOtpHash);

        // Step 2: Submit with wrong OTP -> MUST FAIL
        $wrongOtpResponse = $this->from(route('login'))->post(route('login.login'), [
            'user' => '73334445',
            'otp_code' => '000000',
            'otp_user_id' => $user->id,
        ]);
        $wrongOtpResponse->assertSessionHas('message', 'Código de verificación inválido o expirado.');
        $this->assertGuest();

        // Step 3: Inject known OTP into cache and submit correct OTP
        $validOtp = '582914';
        Cache::put('client_otp_'.$user->id, Hash::make($validOtp), now()->addMinutes(5));

        $step2Response = $this->post(route('login.login'), [
            'user' => '73334445',
            'otp_code' => $validOtp,
            'otp_user_id' => $user->id,
        ]);

        $step2Response->assertRedirect(route('cliente.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Requirement: Generic messages preventing DNI enumeration.
     */
    public function test_generic_error_messages_prevent_dni_enumeration(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'password']);

        // Case 1: DNI does not exist
        $responseNonExistent = $this->from(route('login'))->post(route('login.login'), [
            'user' => '99999999',
            'password' => 'wrongpass',
        ]);

        // Case 2: DNI exists, but wrong password
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '74445556',
            'nombres' => 'CLIENTE EXISTENTE',
            'telefono' => '942333222',
            'direccion' => 'Jr. Test 100',
            'saldo_envases' => 0,
        ]);
        $user = User::create([
            'nombres' => 'CLIENTE EXISTENTE',
            'user' => '74445556',
            'password' => Hash::make('CorrectPassword123'),
            'estado' => 1,
            'idcliente' => $client->id,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $responseExistingWrongPass = $this->from(route('login'))->post(route('login.login'), [
            'user' => '74445556',
            'password' => 'WrongPassword123',
        ]);

        // Both MUST produce the EXACT same generic error message
        $expectedGenericMessage = 'Credenciales incorrectas. Verifique sus datos e intente nuevamente.';
        $responseNonExistent->assertSessionHas('message', $expectedGenericMessage);
        $responseExistingWrongPass->assertSessionHas('message', $expectedGenericMessage);
    }

    /**
     * Requirement: Rate limiting by IP and by DNI/User.
     */
    public function test_failed_attempts_trigger_rate_limiter(): void
    {
        $this->business->update(['auth_cliente_metodo' => 'password']);

        $userKey = 'login_user:locked_dni_test';
        $ipKey = 'login_ip:127.0.0.1';

        RateLimiter::clear($userKey);
        RateLimiter::clear($ipKey);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.login'), [
                'user' => 'locked_dni_test',
                'password' => 'invalid_pass',
            ]);
        }

        // 6th attempt should be blocked by rate limiter
        $lockedResponse = $this->from(route('login'))->post(route('login.login'), [
            'user' => 'locked_dni_test',
            'password' => 'invalid_pass',
        ]);

        $this->assertTrue(RateLimiter::tooManyAttempts($userKey, 5));
        $lockedResponse->assertSessionHas('message');
        $sessionMessage = session('message');
        $this->assertStringContainsString('Demasiados intentos fallidos', $sessionMessage);
    }

    /**
     * Requirement: Reuse EnsureClienteRole middleware and Cliente role (impersonation & role isolation).
     */
    public function test_impersonation_prevention_ensure_cliente_role_middleware(): void
    {
        // 1. Non-client (ADMIN) attempting to access /cliente/dashboard -> MUST BE REJECTED!
        $adminUser = User::create([
            'nombres' => 'ADMIN PRIVILEGIADO',
            'user' => 'admin_isolated',
            'password' => Hash::make('AdminPass123'),
            'estado' => 1,
            'tipo' => 'admin',
        ]);
        $adminUser->assignRole('ADMIN');

        $this->actingAs($adminUser);
        $responseAdminToClient = $this->get(route('cliente.dashboard'));
        $responseAdminToClient->assertRedirect(route('login'));
        $this->assertGuest(); // EnsureClienteRole logs out unauthorized users

        // 2. Client user accessing /cliente/dashboard -> ALLOWED
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '75556667',
            'nombres' => 'CLIENTE VERIFICADO',
            'telefono' => '942444333',
            'direccion' => 'Jr. Valido 123',
            'saldo_envases' => 0,
        ]);
        $clientUser = User::create([
            'nombres' => 'CLIENTE VERIFICADO',
            'user' => '75556667',
            'password' => Hash::make('ClientPass123'),
            'estado' => 1,
            'idcliente' => $client->id,
            'tipo' => 'cliente',
        ]);
        $clientUser->assignRole('Cliente');

        $this->actingAs($clientUser);
        $responseClient = $this->get(route('cliente.dashboard'));
        $responseClient->assertOk();
    }

    /**
     * Requirement: Subsequent orders (QR flow): DNI only; avoid repetitive forms.
     */
    public function test_subsequent_orders_qr_flow_uses_dni_only_avoiding_repetitive_forms(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '76667778',
            'nombres' => 'ROBERTO CARLOS DIAZ',
            'telefono' => '942987654',
            'direccion' => 'Jr. Progreso 321',
            'referencia' => 'Portón azul frente al grifo',
            'coordenadas' => '-6.489000,-76.365000',
            'saldo_envases' => 2,
        ]);

        // Step 1: Client inputs DNI in QR portal check-client endpoint
        $checkResponse = $this->postJson(route('public.order.check_client'), [
            'search' => '76667778',
        ]);

        $checkResponse->assertOk();
        $checkResponse->assertJson([
            'status' => true,
            'found' => true,
            'cliente' => [
                'nombres' => 'ROBERTO CARLOS DIAZ',
                'nro_documento' => '76667778',
                'telefono' => '942987654',
                'direccion' => 'Jr. Progreso 321',
                'referencia' => 'Portón azul frente al grifo',
                'coordenadas' => '-6.489000,-76.365000',
                'saldo_envases' => 2,
            ],
        ]);

        // Step 2: Client places subsequent order without typing location again
        $unit = Unit::firstOrCreate(['codigo' => 'NIU'], ['descripcion' => 'UNIDAD', 'estado' => 1]);
        $product = Product::create([
            'codigo_interno' => 'RECARGA-20L-TEST',
            'descripcion' => 'RECARGA BIDON AGUA 20L',
            'idunidad' => $unit->id,
            'idcategoria' => 1,
            'igv' => 18,
            'precio_compra' => 5.0,
            'precio_venta' => 14.0,
            'opcion' => 1,
            'stock_actual' => 100,
        ]);

        $orderResponse = $this->postJson(route('public.order.store'), [
            'nro_documento' => '76667778',
            'nombres' => 'ROBERTO CARLOS DIAZ',
            'telefono' => '942987654',
            'direccion' => 'Jr. Progreso 321',
            'referencia' => 'Portón azul frente al grifo',
            'coordenadas' => '-6.489000,-76.365000',
            'fecha_programada' => date('Y-m-d'),
            'metodo_pago' => 'efectivo',
            'envases_a_devolver' => 1,
            'items' => [
                [
                    'idproducto' => $product->id,
                    'cantidad' => 2,
                ],
            ],
        ]);

        $orderResponse->assertOk();
        $this->assertDatabaseHas('delivery_orders', [
            'idcliente' => $client->id,
            'direccion_entrega' => 'Jr. Progreso 321',
            'coordenadas' => '-6.489000,-76.365000',
            'total' => 28.00,
        ]);
    }
}
