<?php

namespace Tests\Feature;

use App\Models\Cash;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\IdentityDocumentType;
use App\Models\JugMovement;
use App\Models\LoyaltyPromotion;
use App\Models\LoyaltyPromotionLog;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Water\JugMovementService;
use App\Services\Water\LoyaltyService;
use Carbon\Carbon;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\IdentityDocumentTypeSeeder;
use Database\Seeders\IgvTypeAffectionSeeder;
use Database\Seeders\PayModeSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TypeDocumentSeeder;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoyaltyAndContainerTrackingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected Client $client;

    protected Product $waterProduct;

    protected Warehouse $warehouse;

    protected LoyaltyService $loyaltyService;

    protected JugMovementService $jugService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            TypeDocumentSeeder::class,
            CurrencySeeder::class,
            PayModeSeeder::class,
            IdentityDocumentTypeSeeder::class,
            IgvTypeAffectionSeeder::class,
            WarehouseSeeder::class,
            RoleSeeder::class,
        ]);

        $this->loyaltyService = app(LoyaltyService::class);
        $this->jugService = app(JugMovementService::class);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'nombre' => 'Almacén Central Test',
            'direccion' => 'Jr. San Martín 123',
            'estado' => 1,
        ]);

        $cash = Cash::first() ?? Cash::create([
            'descripcion' => 'Caja Central',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'ADMIN']);

        $this->adminUser = User::create([
            'nombres' => 'ADMIN LOYALTY TEST',
            'user' => 'admin_loyalty_'.uniqid(),
            'password' => bcrypt('password'),
            'estado' => 1,
            'idcaja' => $cash->id,
            'idalmacen' => $this->warehouse->id,
        ]);
        $this->adminUser->syncRoles([$adminRole]);

        $unit = Unit::first() ?? Unit::create(['codigo' => 'NIU', 'descripcion' => 'UNIDAD']);

        $this->waterProduct = Product::create([
            'codigo' => 'AGUA20L-TEST',
            'descripcion' => 'RECARGA AGUA PURIFICADA 20L',
            'precio_venta' => 12.00,
            'opcion' => 1,
            'idunidad' => $unit->id,
            'igv' => 18,
            'stock_actual' => 100,
        ]);

        $docType = IdentityDocumentType::firstOrCreate(['codigo' => '1'], [
            'descripcion' => 'DNI',
            'descripcion_documento' => 'DNI',
            'estado' => 1,
        ]);

        $this->client = Client::create([
            'iddoc' => $docType->id,
            'nro_documento' => '44556677',
            'nombres' => 'CLIENTE TEST FIDELIDAD',
            'telefono' => '942112233',
            'direccion' => 'Jr. Los Cedros 789',
            'codigo_pais' => 'PE',
            'saldo_envases' => 0,
        ]);
    }

    /**
     * Requirement 1 & Acceptance Criteria 1:
     * Changing target from 4 to 5 takes effect immediately without redeployment.
     */
    public function test_changing_target_takes_effect_immediately(): void
    {
        // 1. Configurar meta inicial en 4
        $promotion = LoyaltyPromotion::first();
        if ($promotion) {
            $promotion->update([
                'meta_compras' => 4,
                'bonificacion' => 1,
                'activo' => true,
            ]);
        } else {
            $promotion = LoyaltyPromotion::create([
                'nombre' => 'Fidelidad Test',
                'meta_compras' => 4,
                'bonificacion' => 1,
                'activo' => true,
            ]);
        }

        // Acumular 4 compras para el cliente
        $status = $this->loyaltyService->accumulatePurchases($this->client, 4);
        $this->assertTrue($status['reward_eligible'], 'Con meta=4 y 4 compras acumuladas debe ser elegible.');
        $this->assertEquals(0, $status['remaining_to_free']);

        // 2. Cambiar la meta de 4 a 5 inmediatamente vía base de datos / settings
        $promotion->update([
            'meta_compras' => 5,
            'bonificacion' => 1,
        ]);

        // Verificar el estado inmediatamente sin nuevo despliegue ni reinicio
        $newStatus = $this->loyaltyService->getClientStatus($this->client);
        $this->assertFalse($newStatus['reward_eligible'], 'Al cambiar meta a 5, con 4 compras ya no debe ser elegible aún.');
        $this->assertEquals(5, $newStatus['target']);
        $this->assertEquals(1, $newStatus['remaining_to_free'], 'Debe faltar exactamente 1 compra para la meta de 5.');
        $this->assertEquals('5+1', $newStatus['rule_label']);

        // Acumular la 5ta compra: ahora califica inmediatamente
        $finalStatus = $this->loyaltyService->accumulatePurchases($this->client, 1);
        $this->assertTrue($finalStatus['reward_eligible'], 'Con 5 compras sobre meta 5 debe ser elegible.');
        $this->assertEquals(0, $finalStatus['remaining_to_free']);
    }

    /**
     * Requirement 2:
     * Administration screen: rule changes are recorded in loyalty_promotion_logs audit history.
     */
    public function test_rule_change_history_is_recorded_in_logs(): void
    {
        $this->actingAs($this->adminUser);

        $promotion = LoyaltyPromotion::firstOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Fidelidad Base',
                'meta_compras' => 4,
                'bonificacion' => 1,
                'activo' => true,
            ]
        );

        $response = $this->post(route('loyalty.save_settings'), [
            'nombre' => 'Fidelidad 5+2 Renovada',
            'meta_compras' => 5,
            'bonificacion' => 2,
            'idproducto_objetivo' => $this->waterProduct->id,
            'idproducto_bonificado' => $this->waterProduct->id,
            'activo' => '1',
            'descripcion' => 'Nueva regla acordada en reunión.',
            'motivo_cambio' => 'Actualización de directorio a programa 5+2',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'rule_label' => '5+2',
        ]);

        $log = LoyaltyPromotionLog::where('idpromocion', $promotion->id)->latest('id')->first();
        $this->assertNotNull($log, 'Debe haberse creado un log de auditoría.');
        $this->assertEquals(4, $log->meta_compras_anterior);
        $this->assertEquals(5, $log->meta_compras_nueva);
        $this->assertEquals(1, $log->bonificacion_anterior);
        $this->assertEquals(2, $log->bonificacion_nueva);
        $this->assertEquals('Actualización de directorio a programa 5+2', $log->motivo);
    }

    /**
     * Requirement 3:
     * Free refills do NOT count toward the next target, and redemption preserves surpluses.
     */
    public function test_free_refills_do_not_count_and_redemption_preserves_surpluses(): void
    {
        LoyaltyPromotion::updateOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Fidelidad 5+1',
                'meta_compras' => 5,
                'bonificacion' => 1,
                'activo' => true,
            ]
        );

        // Crear una orden con 2 recargas pagadas y 1 recarga gratis bonificada
        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-SURPLUS-01',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'entregado',
            'direccion_entrega' => 'Calle Las Palmeras 100',
            'fecha_programada' => Carbon::today(),
            'total' => 24.00,
            'bidones_a_entregar' => 3,
        ]);

        // Ítem 1: Recarga pagada
        DeliveryOrderItem::create([
            'iddelivery_order' => $order->id,
            'idproducto' => $this->waterProduct->id,
            'descripcion' => 'Recarga Agua 20L',
            'cantidad' => 2,
            'precio_unitario' => 12.00,
            'subtotal' => 24.00,
            'descuento' => 0.00,
            'total' => 24.00,
            'tipo_item' => 'recarga',
        ]);

        // Ítem 2: Recarga GRATIS (premio de fidelidad)
        DeliveryOrderItem::create([
            'iddelivery_order' => $order->id,
            'idproducto' => $this->waterProduct->id,
            'descripcion' => 'Recarga Agua 20L GRATIS (Fidelidad)',
            'cantidad' => 1,
            'precio_unitario' => 0.00,
            'subtotal' => 0.00,
            'descuento' => 0.00,
            'total' => 0.00,
            'tipo_item' => 'recarga',
        ]);

        // Acumular desde la orden: solo las 2 pagadas deben acumularse, la gratis NO
        $status = $this->loyaltyService->accumulateFromDeliveryOrder($order);
        $this->assertEquals(2, $status['accumulated'], 'Solo deben haberse acumulado 2 compras (la recarga gratis no suma).');

        // Ahora simular que el cliente realiza compras adicionales hasta 7 unidades
        // (meta = 5, por lo tanto 7 compras = 1 premio disponible + 2 de excedente/surplus)
        $statusAfter = $this->loyaltyService->accumulatePurchases($this->client, 5);
        $this->assertEquals(7, $statusAfter['accumulated']);
        $this->assertTrue($statusAfter['reward_eligible']);
        $this->assertEquals(1, $statusAfter['rewards_available']);
        $this->assertEquals(2, $statusAfter['surplus'], 'El excedente debe ser 2 unidades.');

        // Canjear el premio: debe consumir 5 y preservar el excedente de 2
        $redeemResult = $this->loyaltyService->redeemReward($this->client, 1);
        $this->assertTrue($redeemResult['status']);
        $this->assertEquals(2, $redeemResult['new_accumulated'], 'El saldo acumulado restante debe ser el excedente (2).');
        $this->assertEquals(1, $redeemResult['total_claimed'], 'Se debe registrar 1 premio canjeado.');

        // Comprobar estado final del cliente
        $finalStatus = $this->loyaltyService->getClientStatus($this->client);
        $this->assertEquals(2, $finalStatus['accumulated'], 'El excedente de 2 compras permanece para el siguiente ciclo.');
        $this->assertFalse($finalStatus['reward_eligible']);
        $this->assertEquals(3, $finalStatus['remaining_to_free'], 'Ahora faltan 3 compras para el próximo premio.');
    }

    /**
     * Requirement 5 & Acceptance Criteria 2:
     * Centralized accumulation prevents double accumulation when a delivery order is converted into a POS sale.
     */
    public function test_prevent_double_accumulation_between_delivery_and_pos(): void
    {
        LoyaltyPromotion::updateOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Fidelidad 5+1',
                'meta_compras' => 5,
                'bonificacion' => 1,
                'activo' => true,
            ]
        );

        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-ANTI-DUP-01',
            'idcliente' => $this->client->id,
            'idusuario_registro' => $this->adminUser->id,
            'estado' => 'entregado',
            'direccion_entrega' => 'Calle Real 456',
            'fecha_programada' => Carbon::today(),
            'total' => 36.00,
            'bidones_a_entregar' => 3,
        ]);

        DeliveryOrderItem::create([
            'iddelivery_order' => $order->id,
            'idproducto' => $this->waterProduct->id,
            'descripcion' => 'Recarga Agua 20L',
            'cantidad' => 3,
            'precio_unitario' => 12.00,
            'subtotal' => 36.00,
            'descuento' => 0.00,
            'total' => 36.00,
            'tipo_item' => 'recarga',
        ]);

        // 1. Se procesa la liquidación en reparto
        $this->loyaltyService->accumulateFromDeliveryOrder($order);

        $statusAfterDelivery = $this->loyaltyService->getClientStatus($this->client);
        $this->assertEquals(3, $statusAfterDelivery['accumulated'], 'Debe acumular 3 compras en la liquidación.');

        // 2. El pedido se abre en POS para cobrar/facturar (from_delivery = order->id)
        $cartProducts = [
            [
                'id' => $this->waterProduct->id,
                'descripcion' => 'Recarga Agua 20L',
                'cantidad' => 3,
                'precio_venta' => 12.00,
            ],
        ];

        // POS intenta acumular pasando el id de la orden de delivery
        $this->loyaltyService->accumulateFromPosSale($this->client, $cartProducts, $order->id);

        $statusAfterPos = $this->loyaltyService->getClientStatus($this->client);
        $this->assertEquals(3, $statusAfterPos['accumulated'], 'No debe duplicar compras: el total debe mantenerse en 3 compras.');

        // 3. Si se intenta liquidar nuevamente la misma orden, sigue sin duplicar
        $this->loyaltyService->accumulateFromDeliveryOrder($order);
        $this->assertEquals(3, $this->loyaltyService->getClientStatus($this->client)['accumulated']);
    }

    /**
     * Requirement 4:
     * Containers: Total containers per customer summary (in possession + damaged + on loan) and abnormal balance alerts.
     */
    public function test_containers_summary_and_abnormal_balance_alerts(): void
    {
        // 1. Cliente normal con envases en posesión, comodato y dañados
        $this->client->update(['saldo_envases' => 5]);

        // Registrar movimiento de comodato / préstamo (+3)
        JugMovement::create([
            'idcliente' => $this->client->id,
            'tipo_movimiento' => 'nuevo_comodato',
            'entregados_llenos' => 3,
            'devueltos_intactos' => 0,
            'devueltos_danados' => 0,
            'saldo_anterior' => 0,
            'saldo_nuevo' => 3,
            'fecha' => Carbon::now(),
        ]);

        // Registrar devolución con 2 dañados
        JugMovement::create([
            'idcliente' => $this->client->id,
            'tipo_movimiento' => 'devolucion_danados',
            'entregados_llenos' => 0,
            'devueltos_intactos' => 0,
            'devueltos_danados' => 2,
            'costo_dano' => 30.00,
            'saldo_anterior' => 5,
            'saldo_nuevo' => 5,
            'fecha' => Carbon::now(),
        ]);

        $summary = $this->jugService->getClientJugSummary($this->client);
        $this->assertEquals(5, $summary['en_posesion']);
        $this->assertEquals(2, $summary['danados']);
        $this->assertEquals(3, $summary['en_prestamo']);
        $this->assertEquals(10, $summary['total_envases'], 'Total = en posesión (5) + dañados (2) + préstamo (3).');
        $this->assertFalse($summary['es_anormal']);

        // 2. Alerta de saldo negativo (anomalía crítica)
        $this->client->update(['saldo_envases' => -2]);
        $abnormalNegative = $this->jugService->getClientJugSummary($this->client);
        $this->assertTrue($abnormalNegative['es_anormal']);
        $this->assertEquals('danger', $abnormalNegative['tipo_alerta']);
        $this->assertStringContainsString('negativo', $abnormalNegative['mensaje_alerta']);

        // 3. Alerta de saldo elevado (>= 10 envases)
        $this->client->update(['saldo_envases' => 15]);
        $abnormalHigh = $this->jugService->getClientJugSummary($this->client);
        $this->assertTrue($abnormalHigh['es_anormal']);
        $this->assertEquals('warning', $abnormalHigh['tipo_alerta']);
        $this->assertStringContainsString('elevado', $abnormalHigh['mensaje_alerta']);
    }
}
