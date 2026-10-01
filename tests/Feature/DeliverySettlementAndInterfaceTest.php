<?php

namespace Tests\Feature;

use App\Models\ArchingCash;
use App\Models\Business;
use App\Models\Cash;
use App\Models\Client;
use App\Models\ClientLoyalty;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\DeliveryOrderStatusLog;
use App\Models\DetailPayment;
use App\Models\IdentityDocumentType;
use App\Models\JugMovement;
use App\Models\LoyaltyPromotion;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Water\DeliverySettlementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliverySettlementAndInterfaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $cajeroUser;

    protected User $repartidor1;

    protected User $repartidor2;

    protected Client $client;

    protected Product $refillProduct;

    protected Warehouse $warehouse;

    protected Cash $cash;

    protected ArchingCash $activeArqueo;

    protected PayMode $payModeEfectivo;

    protected PayMode $payModeYape;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Business
        Business::create([
            'id' => 1,
            'razon_social' => 'AGUA PURA PALA-SED TEST SAC',
            'ruc' => '20123456789',
            'direccion' => 'Jr. Central 123, Tarapoto',
            'telefono' => '942001122',
        ]);

        $this->seed(\Database\Seeders\TypeDocumentSeeder::class);
        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->seed(\Database\Seeders\IgvTypeAffectionSeeder::class);
        $this->seed(\Database\Seeders\SerieSeeder::class);

        // 2. Identity Document Type
        $doc = IdentityDocumentType::firstOrCreate(
            ['codigo' => '1'],
            ['descripcion' => 'DNI', 'descripcion_documento' => 'DNI', 'estado' => 1]
        );

        // 3. Warehouse and Cash
        $this->warehouse = Warehouse::create([
            'descripcion' => 'Almacén Central',
            'direccion' => 'Jr. Central 123, Tarapoto',
        ]);

        $this->cash = Cash::create([
            'descripcion' => 'Caja Principal 01',
            'estado' => 1,
            'idalmacen' => $this->warehouse->id,
        ]);

        // 4. PayModes
        $this->payModeEfectivo = PayMode::firstOrCreate(['id' => 1], ['descripcion' => 'Efectivo']);
        $this->payModeYape = PayMode::firstOrCreate(['id' => 2], ['descripcion' => 'Yape']);

        // 5. Roles and Permissions
        $pDeliveries = Permission::firstOrCreate(['name' => 'admin.deliveries', 'guard_name' => 'web']);
        $pPos = Permission::firstOrCreate(['name' => 'admin.pos', 'guard_name' => 'web']);

        $roleAdmin = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
        $roleAdmin->syncPermissions([$pDeliveries, $pPos]);

        $roleCajero = Role::firstOrCreate(['name' => 'CAJERO', 'guard_name' => 'web']);
        $roleCajero->syncPermissions([$pDeliveries, $pPos]);

        $roleRepartidor = Role::firstOrCreate(['name' => 'REPARTIDOR', 'guard_name' => 'web']);
        $roleRepartidor->syncPermissions([$pDeliveries]);

        // 6. Users
        $this->adminUser = User::create([
            'nombres' => 'Administrador Sistema',
            'user' => 'admin_test',
            'password' => bcrypt('password123'),
            'estado' => 1,
            'tipo' => 'empleado',
            'idalmacen' => $this->warehouse->id,
        ]);
        $this->adminUser->assignRole('ADMIN');

        $this->cajeroUser = User::create([
            'nombres' => 'Cajero Test',
            'user' => 'cajero_test',
            'password' => bcrypt('password123'),
            'estado' => 1,
            'tipo' => 'empleado',
            'idalmacen' => $this->warehouse->id,
        ]);
        $this->cajeroUser->assignRole('CAJERO');

        $this->repartidor1 = User::create([
            'nombres' => 'Carlos Repartidor Uno',
            'user' => 'driver_1',
            'password' => bcrypt('password123'),
            'estado' => 1,
            'tipo' => 'empleado',
            'idalmacen' => $this->warehouse->id,
        ]);
        $this->repartidor1->assignRole('REPARTIDOR');

        $this->repartidor2 = User::create([
            'nombres' => 'Mario Repartidor Dos',
            'user' => 'driver_2',
            'password' => bcrypt('password123'),
            'estado' => 1,
            'tipo' => 'empleado',
            'idalmacen' => $this->warehouse->id,
        ]);
        $this->repartidor2->assignRole('REPARTIDOR');

        // 7. Active Arching Cash for cashier/driver
        $this->activeArqueo = ArchingCash::create([
            'idcaja' => $this->cash->id,
            'idusuario' => $this->cajeroUser->id,
            'idalmacen' => $this->warehouse->id,
            'fecha_inicio' => Carbon::now()->toDateString(),
            'monto_inicial' => 100.00,
            'total_ventas' => 0,
            'estado' => 1,
        ]);

        // 8. Client
        $this->client = Client::create([
            'iddoc' => $doc->id,
            'nro_documento' => '12345678',
            'nombres' => 'EMPRESA CLIENTE SAC',
            'direccion' => 'Av. Los Próceres 742, Tarapoto',
            'referencia' => 'A espaldas de la plaza',
            'coordenadas' => '-6.480600,-76.361600',
            'telefono' => '942112233',
            'saldo_envases' => 5,
        ]);

        // 9. Product
        $unit = Unit::firstOrCreate(['codigo' => 'NIU'], ['descripcion' => 'Unidad', 'codigo' => 'NIU']);
        $this->refillProduct = Product::create([
            'descripcion' => 'RECARGA AGUA PURIFICADA 20L',
            'precio_venta' => 12.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 50,
        ]);

        // 10. Loyalty Promotion
        LoyaltyPromotion::create([
            'nombre' => 'Promo 4x1 Fidelidad',
            'meta_compras' => 4,
            'bonificacion' => 1,
            'activo' => true,
        ]);
    }

    /**
     * Requirement 1: Google Maps link generation with coordinates, address fallback, and disabled state.
     */
    public function test_maps_links_generated_with_coordinates_route_and_address_fallback(): void
    {
        $service = app(DeliverySettlementService::class);

        // Case A: With valid coordinates
        $orderWithCoords = DeliveryOrder::create([
            'codigo_orden' => 'PED-TEST-001',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'pendiente',
            'direccion_entrega' => 'Av. Los Próceres 742',
            'coordenadas' => '-6.480600,-76.361600',
            'fecha_programada' => Carbon::today(),
            'total' => 24.00,
            'bidones_a_entregar' => 2,
        ]);

        $mapsWithCoords = $service->buildMapsLinks($orderWithCoords);

        $this->assertTrue($mapsWithCoords['has_location']);
        $this->assertTrue($mapsWithCoords['has_coordinates']);
        $this->assertSame('https://www.google.com/maps/search/?api=1&query=-6.480600,-76.361600', $mapsWithCoords['view_url']);
        $this->assertSame('https://www.google.com/maps/dir/?api=1&destination=-6.480600,-76.361600', $mapsWithCoords['route_url']);

        // Case B: Without coordinates, but with text address
        $orderWithAddressOnly = DeliveryOrder::create([
            'codigo_orden' => 'PED-TEST-002',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'pendiente',
            'direccion_entrega' => 'Jr. San Martín 450, Tarapoto',
            'coordenadas' => null,
            'fecha_programada' => Carbon::today(),
            'total' => 36.00,
            'bidones_a_entregar' => 3,
        ]);

        $mapsWithAddr = $service->buildMapsLinks($orderWithAddressOnly);

        $this->assertTrue($mapsWithAddr['has_location']);
        $this->assertFalse($mapsWithAddr['has_coordinates']);
        $this->assertStringContainsString('Jr.+San+Mart%C3%ADn+450%2C+Tarapoto', $mapsWithAddr['view_url']);
        $this->assertStringContainsString('destination=', $mapsWithAddr['route_url']);

        // Case C: Neither coordinates nor address
        $orderEmptyLocation = DeliveryOrder::create([
            'codigo_orden' => 'PED-TEST-003',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'pendiente',
            'direccion_entrega' => '',
            'coordenadas' => null,
            'fecha_programada' => Carbon::today(),
            'total' => 12.00,
            'bidones_a_entregar' => 1,
        ]);

        $mapsEmpty = $service->buildMapsLinks($orderEmptyLocation);

        $this->assertFalse($mapsEmpty['has_location']);
        $this->assertNull($mapsEmpty['view_url']);
        $this->assertNull($mapsEmpty['route_url']);
        $this->assertNotNull($mapsEmpty['message']);
    }

    /**
     * Requirement 2: Role isolation - Repartidor sees ONLY their assigned orders; Admin & Cashier see all.
     */
    public function test_repartidor_sees_only_assigned_orders_and_scoped_kpis(): void
    {
        // Order 1: Assigned to Repartidor 1
        $order1 = DeliveryOrder::create([
            'codigo_orden' => 'PED-R1-001',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'idrepartidor' => $this->repartidor1->id,
            'estado' => 'en_ruta',
            'direccion_entrega' => 'Calle 1',
            'fecha_programada' => Carbon::today(),
            'total' => 50.00,
            'bidones_a_entregar' => 4,
        ]);

        // Order 2: Assigned to Repartidor 2
        $order2 = DeliveryOrder::create([
            'codigo_orden' => 'PED-R2-002',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'idrepartidor' => $this->repartidor2->id,
            'estado' => 'en_ruta',
            'direccion_entrega' => 'Calle 2',
            'fecha_programada' => Carbon::today(),
            'total' => 80.00,
            'bidones_a_entregar' => 6,
        ]);

        // Order 3: Unassigned (Pendiente)
        $order3 = DeliveryOrder::create([
            'codigo_orden' => 'PED-UNASSIGNED',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'idrepartidor' => null,
            'estado' => 'pendiente',
            'direccion_entrega' => 'Calle 3',
            'fecha_programada' => Carbon::today(),
            'total' => 20.00,
            'bidones_a_entregar' => 2,
        ]);

        // Repartidor 1 queries /deliveries/get
        $responseR1 = $this->actingAs($this->repartidor1)->getJson(route('deliveries.get'));
        $responseR1->assertStatus(200);
        $dataR1 = $responseR1->json();

        // Repartidor 1 sees only Order 1
        $codesR1 = collect($dataR1['data'])->pluck('codigo_orden')->all();
        $this->assertContains('PED-R1-001', $codesR1);
        $this->assertNotContains('PED-R2-002', $codesR1);
        $this->assertNotContains('PED-UNASSIGNED', $codesR1);
        $this->assertSame(1, $dataR1['kpis']['en_ruta']);

        // Admin queries /deliveries/get - sees all 3 orders
        $responseAdmin = $this->actingAs($this->adminUser)->getJson(route('deliveries.get'));
        $responseAdmin->assertStatus(200);
        $dataAdmin = $responseAdmin->json();
        $codesAdmin = collect($dataAdmin['data'])->pluck('codigo_orden')->all();
        $this->assertContains('PED-R1-001', $codesAdmin);
        $this->assertContains('PED-R2-002', $codesAdmin);
        $this->assertContains('PED-UNASSIGNED', $codesAdmin);
        $this->assertSame(2, $dataAdmin['kpis']['en_ruta']);
        $this->assertSame(1, $dataAdmin['kpis']['pendientes']);

        // Cashier queries /deliveries/get - has permission and sees all orders
        $responseCajero = $this->actingAs($this->cajeroUser)->getJson(route('deliveries.get'));
        $responseCajero->assertStatus(200);
    }

    /**
     * Requirement 2: Repartidor cannot view or settle an order assigned to someone else.
     */
    public function test_repartidor_cannot_access_or_settle_another_drivers_order(): void
    {
        $orderR2 = DeliveryOrder::create([
            'codigo_orden' => 'PED-FOR-R2',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'idrepartidor' => $this->repartidor2->id,
            'estado' => 'en_ruta',
            'direccion_entrega' => 'Destino R2',
            'fecha_programada' => Carbon::today(),
            'total' => 60.00,
            'bidones_a_entregar' => 5,
        ]);

        // Repartidor 1 attempts to show R2's order
        $respShow = $this->actingAs($this->repartidor1)->getJson(url('deliveries/show/'.$orderR2->id));
        $respShow->assertStatus(403);

        // Repartidor 1 attempts to complete R2's order
        $respComplete = $this->actingAs($this->repartidor1)->postJson(route('deliveries.complete'), [
            'id' => $orderR2->id,
            'bidones_vacios_recibidos' => 5,
            'bidones_danados_recibidos' => 0,
            'cobro_envases_danados' => 0,
            'motivo_liquidacion' => 'despacho_estandar',
        ]);
        $respComplete->assertStatus(403);
    }

    /**
     * Requirement 3 & 4: Full settlement within a single transaction (Invariant B1).
     * Updates: order, jug movements, loyalty points, DetailPayment / cash reconciliation.
     */
    public function test_complete_settlement_in_single_transaction_and_updates_all_invariants(): void
    {
        $initialBalance = $this->client->saldo_envases; // 5

        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-SETTLE-001',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'idrepartidor' => $this->repartidor1->id,
            'idalmacen' => $this->warehouse->id,
            'estado' => 'en_ruta',
            'direccion_entrega' => 'Av. Los Próceres 742',
            'coordenadas' => '-6.480600,-76.361600',
            'telefono_contacto' => '942112233',
            'fecha_programada' => Carbon::today(),
            'subtotal' => 36.00,
            'descuento' => 0.00,
            'total' => 36.00,
            'metodo_pago' => 'yape',
            'estado_pago' => 'pendiente',
            'bidones_a_entregar' => 3,
        ]);

        DeliveryOrderItem::create([
            'iddelivery_order' => $order->id,
            'idproducto' => $this->refillProduct->id,
            'descripcion' => $this->refillProduct->descripcion,
            'tipo_item' => 'recarga',
            'cantidad' => 3,
            'precio_unitario' => 12.00,
            'subtotal' => 36.00,
        ]);

        // Execute settlement via endpoint: 3 delivered full, 2 returned intact, 1 damaged, damage fee S/ 15.00
        $response = $this->actingAs($this->repartidor1)->postJson(route('deliveries.complete'), [
            'id' => $order->id,
            'bidones_vacios_recibidos' => 2,
            'bidones_danados_recibidos' => 1,
            'cobro_envases_danados' => 15.00,
            'motivo_liquidacion' => 'envase_danado',
            'metodo_pago' => 'yape',
            'estado_pago' => 'pagado',
            'notas' => 'Un envase fisurado cobrado al cliente',
        ]);

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertTrue($json['status']);
        $this->assertFalse($json['already_settled']);
        $this->assertNotEmpty($json['whatsapp_url']);

        // Check updated order state
        $order->refresh();
        $this->assertSame('entregado', $order->estado);
        $this->assertNotNull($order->liquidado_at);
        $this->assertSame('envase_danado', $order->motivo_liquidacion);
        $this->assertSame(2, $order->bidones_vacios_recibidos);
        $this->assertSame(1, $order->bidones_danados_recibidos);
        $this->assertEquals(15.00, $order->cobro_envases_danados);
        $this->assertEquals(51.00, $order->total); // 36 subtotal + 15 damage
        $this->assertSame('pagado', $order->estado_pago);
        $this->assertSame($this->activeArqueo->id, $order->idarqueocaja);

        // Check Jug Movement
        $jugMovement = JugMovement::where('iddelivery_order', $order->id)->latest('id')->first();
        $this->assertNotNull($jugMovement);
        $this->assertSame(3, $jugMovement->entregados_llenos);
        $this->assertSame(2, $jugMovement->devueltos_intactos);
        $this->assertSame(1, $jugMovement->devueltos_danados);
        $this->assertEquals(15.00, $jugMovement->costo_dano);

        // Check Client balance: started at 5, delivered 3 (+3), returned 2 intact (-2), 1 damaged and paid for (-1) -> net +0 -> balance stays 5
        $this->client->refresh();
        $this->assertSame($initialBalance, $this->client->saldo_envases);

        // Check Loyalty Points: 3 refills purchased
        $loyalty = ClientLoyalty::where('idcliente', $this->client->id)->first();
        $this->assertNotNull($loyalty);
        $this->assertSame(3, $loyalty->compras_acumuladas);

        // Check DetailPayment linked to active Arqueo (Invariant B1)
        $detailPayment = DetailPayment::where('idarqueocaja', $this->activeArqueo->id)->latest('id')->first();
        $this->assertNotNull($detailPayment);
        $this->assertEquals(51.00, $detailPayment->monto);
        $this->assertSame($this->payModeYape->id, $detailPayment->idpago);

        // Check Audit trail in delivery_order_status_logs
        $auditLog = DeliveryOrderStatusLog::where('iddelivery_order', $order->id)->latest('id')->first();
        $this->assertNotNull($auditLog);
        $this->assertSame('en_ruta', $auditLog->estado_anterior);
        $this->assertSame('entregado', $auditLog->estado_nuevo);
        $this->assertSame('envase_danado', $auditLog->motivo);
        $this->assertSame($this->repartidor1->id, $auditLog->idusuario);
    }

    /**
     * Requirement 3 (Acceptance Criteria): Settlement process is strictly idempotent.
     * Double-clicking or repeating settlement must NOT duplicate movements or charges.
     */
    public function test_settlement_process_is_idempotent_no_duplicate_charges_or_movements(): void
    {
        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-IDEMP-001',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'idrepartidor' => $this->repartidor1->id,
            'idalmacen' => $this->warehouse->id,
            'estado' => 'en_ruta',
            'direccion_entrega' => 'Av. Los Próceres 742',
            'fecha_programada' => Carbon::today(),
            'subtotal' => 24.00,
            'total' => 24.00,
            'metodo_pago' => 'efectivo',
            'bidones_a_entregar' => 2,
        ]);

        DeliveryOrderItem::create([
            'iddelivery_order' => $order->id,
            'idproducto' => $this->refillProduct->id,
            'descripcion' => $this->refillProduct->descripcion,
            'tipo_item' => 'recarga',
            'cantidad' => 2,
            'precio_unitario' => 12.00,
            'subtotal' => 24.00,
        ]);

        // First settlement call
        $firstCall = $this->actingAs($this->repartidor1)->postJson(route('deliveries.complete'), [
            'id' => $order->id,
            'bidones_vacios_recibidos' => 2,
            'bidones_danados_recibidos' => 0,
            'cobro_envases_danados' => 0,
            'motivo_liquidacion' => 'despacho_estandar',
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'pagado',
        ]);

        $firstCall->assertStatus(200);
        $this->assertFalse($firstCall->json('already_settled'));

        $movementsCountBefore = JugMovement::where('iddelivery_order', $order->id)->count();
        $paymentsCountBefore = DetailPayment::where('idarqueocaja', $this->activeArqueo->id)->count();
        $loyaltyPointsBefore = ClientLoyalty::where('idcliente', $this->client->id)->value('compras_acumuladas');

        // Second settlement call on already settled order (double-click simulation)
        $secondCall = $this->actingAs($this->repartidor1)->postJson(route('deliveries.complete'), [
            'id' => $order->id,
            'bidones_vacios_recibidos' => 2,
            'bidones_danados_recibidos' => 0,
            'cobro_envases_danados' => 0,
            'motivo_liquidacion' => 'despacho_estandar',
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'pagado',
        ]);

        $secondCall->assertStatus(200);
        $this->assertTrue($secondCall->json('already_settled'));

        // Verify zero duplicates
        $movementsCountAfter = JugMovement::where('iddelivery_order', $order->id)->count();
        $paymentsCountAfter = DetailPayment::where('idarqueocaja', $this->activeArqueo->id)->count();
        $loyaltyPointsAfter = ClientLoyalty::where('idcliente', $this->client->id)->value('compras_acumuladas');

        $this->assertSame($movementsCountBefore, $movementsCountAfter);
        $this->assertSame($paymentsCountBefore, $paymentsCountAfter);
        $this->assertSame($loyaltyPointsBefore, $loyaltyPointsAfter);
    }

    /**
     * Requirement 5: State Machine prevents invalid transitions and logs audit trail.
     */
    public function test_state_machine_prevents_invalid_transitions_and_logs_audit_trail(): void
    {
        $service = app(DeliverySettlementService::class);

        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-STATE-001',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'pendiente',
            'direccion_entrega' => 'Destino Prueba',
            'fecha_programada' => Carbon::today(),
            'total' => 24.00,
            'bidones_a_entregar' => 2,
        ]);

        // Transition 1: pendiente -> en_ruta (Valid)
        $service->transition($order, DeliverySettlementService::STATUS_EN_RUTA, 'Asignación a chofer', 'Despachado a ruta', userId: $this->adminUser->id);
        $this->assertSame('en_ruta', $order->fresh()->estado);

        // Check audit log for transition 1
        $this->assertDatabaseHas('delivery_order_status_logs', [
            'iddelivery_order' => $order->id,
            'estado_anterior' => 'pendiente',
            'estado_nuevo' => 'en_ruta',
            'motivo' => 'Asignación a chofer',
        ]);

        // Transition 2: en_ruta -> entregado (Valid)
        $service->transition($order, DeliverySettlementService::STATUS_ENTREGADO, 'Entrega en domicilio', userId: $this->repartidor1->id);
        $this->assertSame('entregado', $order->fresh()->estado);

        // Transition 3: entregado -> pendiente (INVALID - estado final)
        $this->expectException(\InvalidArgumentException::class);
        $service->transition($order, DeliverySettlementService::STATUS_PENDIENTE, 'Revertir', userId: $this->adminUser->id);
    }

    /**
     * Requirement 4: Container tracking summary view endpoint returns aggregate metrics.
     */
    public function test_containers_summary_endpoint_returns_accurate_metrics(): void
    {
        // Place an order with 2 damaged containers
        DeliveryOrder::create([
            'codigo_orden' => 'PED-CONT-001',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'entregado',
            'direccion_entrega' => 'Av. Los Próceres 742',
            'fecha_programada' => Carbon::today(),
            'total' => 30.00,
            'bidones_a_entregar' => 10,
            'bidones_vacios_recibidos' => 8,
            'bidones_danados_recibidos' => 2,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('deliveries.containers_summary'));
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertTrue($json['status']);
        $summary = $json['summary'];

        $this->assertSame($this->client->saldo_envases, $summary['total_prestados']);
        $this->assertGreaterThanOrEqual(2, $summary['total_danados']);
        $this->assertGreaterThanOrEqual(10, $summary['total_entregados']);
        $this->assertGreaterThanOrEqual(8, $summary['total_devueltos']);
    }

    /**
     * Requirement: Verify /deliveries HTML view loads without SQL column errors.
     */
    public function test_deliveries_view_loads_successfully_without_database_errors(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.deliveries'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.deliveries.list');
        $response->assertViewHas('payModes');
    }
}
