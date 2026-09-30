<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\IdentityDocumentType;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderPlacementFlowTest extends TestCase
{
    use RefreshDatabase;

    protected int $docId;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar negocio
        Business::create([
            'id' => 1,
            'razon_social' => 'AGUA PURA TEST SAC',
            'ruc' => '20123456789',
            'direccion' => 'Jr. Central 123, Tarapoto',
            'telefono' => '942001122',
            'auth_cliente_metodo' => 'password',
        ]);

        // Tipo de documento DNI
        $doc = IdentityDocumentType::firstOrCreate(
            ['codigo' => '1'],
            [
                'descripcion' => 'DNI',
                'descripcion_documento' => 'DNI',
                'estado' => 1,
            ]
        );
        $this->docId = (int) $doc->id;

        // Métodos de pago
        PayMode::create(['id' => 1, 'descripcion' => 'Efectivo']);
        PayMode::create(['id' => 2, 'descripcion' => 'Yape']);
        PayMode::create(['id' => 3, 'descripcion' => 'Plin']);
        PayMode::create(['id' => 7, 'descripcion' => 'Transferencia']);

        Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
    }

    /**
     * Test 1: Cliente registrado en portal nuevo pedido tiene sus datos pre-cargados
     */
    public function test_registered_customer_can_view_order_page_with_prefilled_default_address(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '44556677',
            'nombres' => 'JUAN PEREZ FLORES',
            'direccion' => 'JR. ALFONSO UGARTE 450',
            'referencia' => 'FRENTE A BODEGA DON PEPE',
            'coordenadas' => '-6.480600,-76.361600',
            'telefono' => '942112233',
            'saldo_envases' => 2,
        ]);

        $user = User::create([
            'nombres' => 'JUAN PEREZ FLORES',
            'user' => '44556677',
            'password' => bcrypt('password123'),
            'idcliente' => $client->id,
            'estado' => 1,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $unit = Unit::create(['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        Product::create([
            'descripcion' => 'RECARGA AGUA PURIFICADA 20L',
            'precio_venta' => 12.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('cliente.order'));

        $response->assertStatus(200);
        $response->assertSee('JR. ALFONSO UGARTE 450');
        $response->assertSee('FRENTE A BODEGA DON PEPE');
        $response->assertSee('-6.480600,-76.361600');
        $response->assertSee('Dirección principal registrada');
        $response->assertSee('Enviar a otra dirección para este pedido');
        $response->assertSee('Envases vacíos que retornarás');
    }

    /**
     * Test 2: Cliente registrado realiza pedido exitoso en el portal usando su dirección por defecto
     */
    public function test_registered_customer_places_order_with_default_address_in_portal(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '44556677',
            'nombres' => 'JUAN PEREZ FLORES',
            'direccion' => 'JR. ALFONSO UGARTE 450',
            'referencia' => 'FRENTE A BODEGA DON PEPE',
            'coordenadas' => '-6.480600,-76.361600',
            'telefono' => '942112233',
            'saldo_envases' => 2,
        ]);

        $user = User::create([
            'nombres' => 'JUAN PEREZ FLORES',
            'user' => '44556677',
            'password' => bcrypt('password123'),
            'idcliente' => $client->id,
            'estado' => 1,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $unit = Unit::create(['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        $product = Product::create([
            'descripcion' => 'RECARGA AGUA PURIFICADA 20L',
            'precio_venta' => 12.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 100,
        ]);

        $payload = [
            'direccion_entrega' => 'JR. ALFONSO UGARTE 450',
            'referencia' => 'FRENTE A BODEGA DON PEPE',
            'coordenadas' => '-6.480600,-76.361600',
            'fecha_programada' => Carbon::now()->addDay()->format('Y-m-d'),
            'franja_horaria' => 'manana',
            'metodo_pago' => 'yape',
            'envases_a_devolver' => 2,
            'notas' => 'Tocar timbre fuerte',
            'items' => [
                [
                    'idproducto' => $product->id,
                    'cantidad' => 2,
                ],
            ],
            'device_timestamp' => Carbon::now()->toIso8601String(),
        ];

        $response = $this->actingAs($user)->postJson(route('cliente.order.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);
        $this->assertStringStartsWith('PED-', $response->json('order_code'));
        $this->assertNotEmpty($response->json('tracking_url'));

        // Verificar registro en base de datos
        $order = DeliveryOrder::where('codigo_orden', $response->json('order_code'))->first();
        $this->assertNotNull($order);
        $this->assertEquals($client->id, $order->idcliente);
        $this->assertEquals($user->id, $order->idusuario_registro);
        $this->assertEquals('portal', $order->origen);
        $this->assertEquals('JR. ALFONSO UGARTE 450', $order->direccion_entrega);
        $this->assertEquals(2, $order->bidones_vacios_recibidos);
        $this->assertEquals('yape', $order->metodo_pago);
        $this->assertEquals(24.00, $order->total);

        // La dirección del cliente permanece intacta
        $client->refresh();
        $this->assertEquals('JR. ALFONSO UGARTE 450', $client->direccion);
        $this->assertEquals('FRENTE A BODEGA DON PEPE', $client->referencia);
    }

    /**
     * Test 3: Opción "Enviar a otra dirección" NO sobrescribe la dirección por defecto del cliente
     */
    public function test_alternative_address_order_does_not_overwrite_customer_default_address(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '44556677',
            'nombres' => 'MARIA DEL CARMEN',
            'direccion' => 'AV. LIMA 100, TARAPOTO',
            'referencia' => 'PORTON BLANCO',
            'coordenadas' => '-6.480000,-76.360000',
            'telefono' => '942998877',
            'saldo_envases' => 1,
        ]);

        $user = User::create([
            'nombres' => 'MARIA DEL CARMEN',
            'user' => '44556677',
            'password' => bcrypt('password123'),
            'idcliente' => $client->id,
            'estado' => 1,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $unit = Unit::create(['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        $product = Product::create([
            'descripcion' => 'BIDON CON AGUA PURIFICADA 20L NUEVO',
            'precio_venta' => 45.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 50,
        ]);

        $payload = [
            'enviar_otra_direccion' => true,
            'direccion_entrega' => 'OFICINA: JR. SAN MARTIN 890, MORALES',
            'referencia' => 'PISO 2 OFICINA 201',
            'coordenadas' => '-6.490000,-76.370000',
            'fecha_programada' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'franja_horaria' => 'tarde',
            'metodo_pago' => 'transferencia',
            'envases_a_devolver' => 0,
            'notas' => 'Entregar en recepción',
            'items' => [
                [
                    'idproducto' => $product->id,
                    'cantidad' => 1,
                ],
            ],
            'device_timestamp' => Carbon::now()->toIso8601String(),
        ];

        $response = $this->actingAs($user)->postJson(route('cliente.order.store'), $payload);

        $response->assertStatus(200);
        $orderCode = $response->json('order_code');

        $order = DeliveryOrder::where('codigo_orden', $orderCode)->first();
        $this->assertNotNull($order);
        // La orden tiene la dirección alternativa
        $this->assertEquals('OFICINA: JR. SAN MARTIN 890, MORALES', $order->direccion_entrega);
        $this->assertEquals('PISO 2 OFICINA 201', $order->referencia);
        $this->assertEquals('-6.490000,-76.370000', $order->coordenadas);

        // INVARIANTE CRÍTICA: La dirección predeterminada del cliente NO fue modificada
        $client->refresh();
        $this->assertEquals('AV. LIMA 100, TARAPOTO', $client->direccion);
        $this->assertEquals('PORTON BLANCO', $client->referencia);
        $this->assertEquals('-6.480000,-76.360000', $client->coordenadas);
    }

    /**
     * Test 4: Canal QR público con cliente reconocido y opción de enviar a otra dirección
     */
    public function test_qr_order_with_existing_client_and_alternative_address_preserves_master_address(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '70112233',
            'nombres' => 'CARLOS RODRIGUEZ',
            'direccion' => 'CALLE LAS PALMERAS 330, MORALES',
            'referencia' => 'CASA ESQUINA REJAS NEGRAS',
            'coordenadas' => '-6.482000,-76.365000',
            'telefono' => '955443322',
            'saldo_envases' => 3,
        ]);

        $unit = Unit::create(['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        $product = Product::create([
            'descripcion' => 'RECARGA AGUA PURIFICADA 20L',
            'precio_venta' => 12.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 100,
        ]);

        // Simular consulta inicial por DNI o teléfono
        $checkResponse = $this->postJson(route('public.order.check_client'), [
            'search' => '70112233',
        ]);
        $checkResponse->assertStatus(200);
        $checkResponse->assertJson([
            'status' => true,
            'found' => true,
            'cliente' => [
                'direccion' => 'CALLE LAS PALMERAS 330, MORALES',
                'referencia' => 'CASA ESQUINA REJAS NEGRAS',
            ],
        ]);

        // Registrar pedido con otra dirección
        $qrPayload = [
            'nombres' => 'CARLOS RODRIGUEZ',
            'telefono' => '955443322',
            'nro_documento' => '70112233',
            'enviar_otra_direccion' => true,
            'direccion' => 'TALLER MECANICO: JR. BOLOGNESI 120',
            'referencia' => 'AL COSTADO DEL GRIFO',
            'coordenadas' => '-6.495000,-76.375000',
            'fecha_programada' => Carbon::now()->addDay()->format('Y-m-d'),
            'franja_horaria' => 'noche',
            'metodo_pago' => 'plin',
            'envases_a_devolver' => 1,
            'items' => [
                [
                    'idproducto' => $product->id,
                    'cantidad' => 1,
                ],
            ],
            'device_timestamp' => Carbon::now()->toIso8601String(),
        ];

        $response = $this->postJson(route('public.order.store'), $qrPayload);

        $response->assertStatus(200);
        $orderCode = $response->json('order_code');

        $order = DeliveryOrder::where('codigo_orden', $orderCode)->first();
        $this->assertEquals('TALLER MECANICO: JR. BOLOGNESI 120', $order->direccion_entrega);
        $this->assertEquals('AL COSTADO DEL GRIFO', $order->referencia);
        $this->assertEquals('noche', $order->franja_horaria);
        $this->assertEquals('plin', $order->metodo_pago);
        $this->assertEquals(1, $order->bidones_vacios_recibidos);

        // Verificar que el cliente maestro en la base de datos conserva su dirección original
        $client->refresh();
        $this->assertEquals('CALLE LAS PALMERAS 330, MORALES', $client->direccion);
        $this->assertEquals('CASA ESQUINA REJAS NEGRAS', $client->referencia);
        $this->assertEquals('-6.482000,-76.365000', $client->coordenadas);
    }

    /**
     * Test 5: Validación server-side estricta (items requeridos, fecha válida, método de pago)
     */
    public function test_server_side_validation_fails_for_invalid_order_data(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '44556677',
            'nombres' => 'TEST CLIENT',
            'direccion' => 'JR. LIMA 100',
            'telefono' => '942112233',
        ]);

        $user = User::create([
            'nombres' => 'TEST CLIENT',
            'user' => '44556677',
            'password' => bcrypt('password123'),
            'idcliente' => $client->id,
            'estado' => 1,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        // Sin items
        $response = $this->actingAs($user)->postJson(route('cliente.order.store'), [
            'direccion_entrega' => 'JR. LIMA 100',
            'fecha_programada' => Carbon::now()->addDay()->format('Y-m-d'),
            'metodo_pago' => 'efectivo',
            'items' => [],
        ]);
        $response->assertStatus(422);

        // Fecha anterior a hoy
        $unit = Unit::create(['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        $product = Product::create([
            'descripcion' => 'AGUA TEST',
            'precio_venta' => 10,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 10,
        ]);

        $responsePast = $this->actingAs($user)->postJson(route('cliente.order.store'), [
            'direccion_entrega' => 'JR. LIMA 100',
            'fecha_programada' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'metodo_pago' => 'efectivo',
            'items' => [['idproducto' => $product->id, 'cantidad' => 1]],
        ]);
        $responsePast->assertStatus(422);
    }

    /**
     * Test 6: Detección y log de desfase entre hora del dispositivo y hora del servidor
     */
    public function test_timestamp_divergence_logs_warning_and_preserves_server_time(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '11223344',
            'nombres' => 'TIME DIVERGENCE TEST',
            'direccion' => 'JR. TIME 123',
            'telefono' => '942000000',
        ]);

        $user = User::create([
            'nombres' => 'TIME DIVERGENCE TEST',
            'user' => '11223344',
            'password' => bcrypt('secret'),
            'idcliente' => $client->id,
            'estado' => 1,
            'tipo' => 'cliente',
        ]);
        $user->assignRole('Cliente');

        $unit = Unit::create(['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        $product = Product::create([
            'descripcion' => 'RECARGA AGUA 20L',
            'precio_venta' => 12.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 20,
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Diferencia detectada entre timestamp del dispositivo y servidor')
                    && isset($context['diff_minutes'])
                    && $context['diff_minutes'] > 15;
            });

        Log::shouldReceive('info')->atLeast()->once();

        // Dispositivo envía timestamp con 3 horas de adelanto
        $divergentDeviceTime = Carbon::now()->addHours(3)->toIso8601String();

        $response = $this->actingAs($user)->postJson(route('cliente.order.store'), [
            'direccion_entrega' => 'JR. TIME 123',
            'fecha_programada' => Carbon::now()->addDay()->format('Y-m-d'),
            'metodo_pago' => 'efectivo',
            'items' => [['idproducto' => $product->id, 'cantidad' => 1]],
            'device_timestamp' => $divergentDeviceTime,
        ]);

        $response->assertStatus(200);
        $order = DeliveryOrder::where('codigo_orden', $response->json('order_code'))->first();
        $this->assertNotNull($order);
        // La fuente de verdad del servidor se mantiene para created_at
        $this->assertEquals(Carbon::now()->format('Y-m-d'), $order->created_at->format('Y-m-d'));
    }

    /**
     * Test 7: Comprobante y vista de seguimiento con línea de tiempo en /pedido/seguimiento/{code}
     */
    public function test_tracking_receipt_displays_status_timeline_and_order_details(): void
    {
        $client = Client::create([
            'iddoc' => $this->docId,
            'nro_documento' => '88990011',
            'nombres' => 'ANA GOMEZ PAREDES',
            'direccion' => 'JR. LEGUIA 780, TARAPOTO',
            'referencia' => 'A ESPALDAS DEL COLEGIO',
            'telefono' => '942556677',
        ]);

        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-999888',
            'idcliente' => $client->id,
            'origen' => 'qr',
            'estado' => 'pendiente',
            'direccion_entrega' => 'JR. LEGUIA 780, TARAPOTO',
            'referencia' => 'A ESPALDAS DEL COLEGIO',
            'telefono_contacto' => '942556677',
            'fecha_programada' => Carbon::now()->addDay()->format('Y-m-d'),
            'franja_horaria' => 'manana',
            'subtotal' => 24.00,
            'descuento' => 0.00,
            'total' => 24.00,
            'metodo_pago' => 'yape',
            'estado_pago' => 'pendiente',
            'bidones_a_entregar' => 2,
            'bidones_vacios_recibidos' => 2,
            'notas' => 'Pedido QR de prueba',
        ]);

        $response = $this->get(route('public.order.tracking', ['code' => 'PED-999888']));

        $response->assertStatus(200);
        $response->assertSee('PED-999888');
        $response->assertSee('1. Pedido Recibido');
        $response->assertSee('2. En Ruta de Entrega');
        $response->assertSee('3. Entregado con Éxito');
        $response->assertSee('JR. LEGUIA 780, TARAPOTO');
        $response->assertSee('ANA GOMEZ PAREDES');
        $response->assertSee('Envases vacíos a retornar');
        $response->assertSee('2 bidón(es)');
        $response->assertSee('S/ 24.00');
    }
}
