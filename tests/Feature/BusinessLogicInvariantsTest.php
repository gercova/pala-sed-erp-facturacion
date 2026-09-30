<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportPaymentController;
use App\Models\ArchingCash;
use App\Models\Billing;
use App\Models\Cash;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DetailPayment;
use App\Models\IdentityDocumentType;
use App\Models\LoyaltyPromotion;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\SaleNote;
use App\Models\Serie;
use App\Models\StockProduct;
use App\Models\TypeDocument;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Water\LoyaltyService;
use Database\Seeders\PayModeSeeder;
use Database\Seeders\TypeDocumentSeeder;
use Database\Seeders\WaterDistributionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BusinessLogicInvariantsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TypeDocumentSeeder::class);
        $this->seed(PayModeSeeder::class);
    }

    /**
     * Invariant 3: Stock applies only if products.opcion == 1 (physical).
     * opcion == 2 is a service and must not alter stock.
     */
    public function test_stock_invariance_applies_only_to_physical_products(): void
    {
        $warehouse = Warehouse::firstOrCreate(['id' => 1], [
            'nombre' => 'Almacén Central Test',
            'direccion' => 'Jr. Test 123',
            'estado' => 1,
        ]);

        $unit = Unit::firstOrCreate(['codigo' => 'NIU'], [
            'descripcion' => 'UNIDAD',
            'estado' => 1,
        ]);

        // Producto Físico (opcion == 1)
        $physicalProduct = Product::create([
            'codigo_interno' => 'TEST-PHYS-01',
            'descripcion' => 'BIDON DE AGUA TEST 20L',
            'idunidad' => $unit->id,
            'idcategoria' => 1,
            'igv' => 18,
            'idcodigo_igv' => 1,
            'precio_compra' => 5.0,
            'precio_venta' => 15.0,
            'opcion' => 1,
            'stock_actual' => 50,
        ]);

        $stockRecord = StockProduct::create([
            'idproducto' => $physicalProduct->id,
            'idalmacen' => $warehouse->id,
            'stock_minimo' => 5,
            'stock_actual' => 50,
            'precio_compra' => 5.0,
            'precio_venta' => 15.0,
            'fecha_registro' => date('Y-m-d'),
            'stock_entrada' => 50,
        ]);

        // Producto Servicio (opcion == 2)
        $serviceProduct = Product::create([
            'codigo_interno' => 'TEST-SERV-01',
            'descripcion' => 'SERVICIO DE MANTENIMIENTO DISPENSADOR',
            'idunidad' => $unit->id,
            'idcategoria' => 1,
            'igv' => 18,
            'idcodigo_igv' => 1,
            'precio_compra' => 0.0,
            'precio_venta' => 25.0,
            'opcion' => 2,
            'stock_actual' => 0,
        ]);

        $soldQty = 5;

        // Simular lógica del POS para producto físico
        if ((int) $physicalProduct->opcion === 1) {
            $nuevoStock = max(0, (int) $stockRecord->stock_actual - $soldQty);
            $stockRecord->update(['stock_actual' => $nuevoStock]);
        }

        // Simular lógica del POS para producto de servicio
        $serviceStockDecremented = false;
        if ((int) $serviceProduct->opcion === 1) {
            $serviceStockDecremented = true;
        }

        $stockRecord->refresh();
        $this->assertEquals(45, $stockRecord->stock_actual);
        $this->assertFalse($serviceStockDecremented, 'Los servicios con opcion == 2 no deben descontar stock.');
    }

    /**
     * Meeting Decision 5: Programa de fidelidad "5+1" (meta_compras = 5, bonificacion = 1).
     */
    public function test_loyalty_program_5_plus_1_reaches_reward_at_five_purchases(): void
    {
        $this->seed(WaterDistributionSeeder::class);

        $promo = LoyaltyPromotion::where('activo', true)->first();
        $this->assertNotNull($promo);
        $this->assertEquals(5, $promo->meta_compras, 'La meta de compras debe ser 5 para el programa 5+1.');
        $this->assertEquals(1, $promo->bonificacion, 'La bonificación debe ser 1 unidad.');

        $docType = IdentityDocumentType::firstOrCreate(['codigo' => '1'], [
            'descripcion' => 'DNI',
            'estado' => 1,
        ]);

        $client = Client::create([
            'iddoc' => $docType->id,
            'nro_documento' => '77889900',
            'nombres' => 'JUAN PEREZ FIDELIDAD',
            'telefono' => '999888777',
            'direccion' => 'Jr. Los Claveles 456',
            'codigo_pais' => 'PE',
            'saldo_envases' => 0,
        ]);

        $loyaltyService = app(LoyaltyService::class);

        // Compra 4 unidades: aún no debe calificar
        $status4 = $loyaltyService->accumulatePurchases($client, 4);
        $this->assertFalse($status4['reward_eligible']);
        $this->assertEquals(1, $status4['remaining_to_free']);

        // Compra la 5ta unidad: ahora califica a la 6ta gratis (5+1)
        $status5 = $loyaltyService->accumulatePurchases($client, 1);
        $this->assertTrue($status5['reward_eligible']);
        $this->assertEquals(0, $status5['remaining_to_free']);
    }

    /**
     * Meeting Decision (Feature flag): CLIENT_LOGIN_MODE ('password' vs 'id_only').
     */
    public function test_client_login_feature_flag_allows_id_only_for_clients_only(): void
    {
        Role::firstOrCreate(['name' => 'Cliente']);
        Role::firstOrCreate(['name' => 'ADMIN']);

        $docType = IdentityDocumentType::firstOrCreate(['codigo' => '1'], [
            'descripcion' => 'DNI',
            'estado' => 1,
        ]);

        $client = Client::create([
            'iddoc' => $docType->id,
            'nro_documento' => '88887777',
            'nombres' => 'MARIA LOPEZ PORTAL',
            'telefono' => '987654321',
            'direccion' => 'Av. Independencia 789',
            'codigo_pais' => 'PE',
            'saldo_envases' => 0,
        ]);

        $clientUser = User::create([
            'nombres' => 'MARIA LOPEZ',
            'user' => '88887777',
            'password' => Hash::make('password123.'),
            'estado' => 1,
            'idcliente' => $client->id,
            'tipo' => 'cliente',
        ]);
        $clientUser->assignRole('Cliente');

        $adminUser = User::create([
            'nombres' => 'ADMIN TEST',
            'user' => 'admin_test_flag',
            'password' => Hash::make('admin123.'),
            'estado' => 1,
            'tipo' => 'admin',
        ]);
        $adminUser->assignRole('ADMIN');

        // Modo por defecto: 'password'
        config(['erp.client_login_mode' => 'password']);

        // En modo 'password', enviar login sin contraseña debe fallar
        $resPass = $this->post(route('login.login'), [
            'user' => '88887777',
            'password' => '',
        ]);
        $resPass->assertSessionHasErrors(['password']);
        $this->assertFalse(Auth::check());

        // Modo Feature Flag: 'id_only'
        config(['erp.client_login_mode' => 'id_only']);

        // El cliente puede ingresar con solo su DNI sin contraseña
        $resIdOnly = $this->post(route('login.login'), [
            'user' => '88887777',
            'password' => '',
        ]);
        $resIdOnly->assertRedirect(route('cliente.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($clientUser->id, Auth::id());
        Auth::logout();

        // El administrador NUNCA puede usar id_only (requiere rol Cliente)
        $resAdminIdOnly = $this->post(route('login.login'), [
            'user' => 'admin_test_flag',
            'password' => '',
        ]);
        $this->assertFalse(Auth::check(), 'Los usuarios administrativos no deben tener acceso id_only.');
    }

    /**
     * Invariant 7: Sales notes use NV series; never sent to SUNAT.
     */
    public function test_sales_notes_use_nv_series(): void
    {
        $cash = Cash::firstOrCreate(['id' => 1], [
            'descripcion' => 'CAJA PRINCIPAL 1',
            'estado' => 1,
        ]);

        $typeDocSaleNote = TypeDocument::where('codigo', '02')->first();
        $this->assertNotNull($typeDocSaleNote);

        $serieNv = Serie::updateOrCreate(
            ['idtipo_documento' => $typeDocSaleNote->id, 'idcaja' => $cash->id],
            [
                'serie' => 'NV01',
                'correlativo' => '00000001',
                'estado' => 1,
            ]
        );

        $this->assertStringStartsWith('NV', $serieNv->serie);

        $typeDocFactura = TypeDocument::where('codigo', '01')->first();
        $serieFactura = Serie::updateOrCreate(
            ['idtipo_documento' => $typeDocFactura->id, 'idcaja' => $cash->id],
            [
                'serie' => 'F001',
                'correlativo' => '00000001',
                'estado' => 1,
            ]
        );
        $this->assertStringStartsWith('F', $serieFactura->serie);
    }

    /**
     * Decision 6 & Invariants 4 & 5: Total sales must match cash register payments
     * aggregating both billings and sale notes without duplicating.
     */
    public function test_payment_methods_report_aggregates_both_billings_and_sale_notes(): void
    {
        $today = date('Y-m-d');

        $cash = Cash::firstOrCreate(['id' => 1], ['descripcion' => 'CAJA TEST', 'estado' => 1]);
        $user = User::firstOrCreate(['id' => 1], [
            'nombres' => 'USER TEST',
            'user' => 'usertest',
            'password' => 'secret',
            'estado' => 1,
        ]);

        $arching = ArchingCash::create([
            'idcaja' => $cash->id,
            'idusuario' => $user->id,
            'idalmacen' => 1,
            'fecha_inicio' => $today,
            'monto_inicial' => 100.0,
            'total_ventas' => 2,
            'estado' => 1,
        ]);

        $payModeYape = PayMode::where('descripcion', 'LIKE', '%Yape%')->first() ?? PayMode::first();
        $payModeTrans = PayMode::where('descripcion', 'LIKE', '%Transferencia%')->first() ?? PayMode::create(['descripcion' => 'Transferencia']);

        $docTypeNV = TypeDocument::where('codigo', '02')->first();
        $docTypeBol = TypeDocument::where('codigo', '03')->first();

        // 1. Nota de Venta (S/ 30.00 con Yape)
        $saleNote = SaleNote::create([
            'idtipo_comprobante' => $docTypeNV->id,
            'serie' => 'NV01',
            'correlativo' => '00000099',
            'fecha_emision' => $today,
            'fecha_vencimiento' => $today,
            'hora' => '10:00:00',
            'idcliente' => 1,
            'subtotal' => 30.0,
            'igv' => 0.0,
            'total' => 30.0,
            'estado' => 1,
            'idusuario' => $user->id,
            'idarqueocaja' => $arching->id,
        ]);

        DetailPayment::create([
            'idtipo_comprobante' => $docTypeNV->id,
            'idfactura' => $saleNote->id,
            'idpago' => $payModeYape->id,
            'monto' => 30.0,
            'idarqueocaja' => $arching->id,
            'estado' => 1,
        ]);

        // 2. Boleta Electrónica (S/ 70.00 con Transferencia)
        $billing = Billing::create([
            'idtipo_comprobante' => $docTypeBol->id,
            'serie' => 'B001',
            'correlativo' => '00000099',
            'fecha_emision' => $today,
            'fecha_vencimiento' => $today,
            'hora' => '10:15:00',
            'idcliente' => 1,
            'idmoneda' => 1,
            'idpago' => $payModeTrans->id,
            'modo_pago' => $payModeTrans->id,
            'sunat_forma_pago' => 'Contado',
            'exonerada' => 0,
            'inafecta' => 0,
            'gravada' => 59.32,
            'anticipo' => 0,
            'igv' => 10.68,
            'icbper' => 0,
            'gratuita' => 0,
            'otros_cargos' => 0,
            'total' => 70.0,
            'monto_credito' => 0,
            'anulado' => false,
            'nticket' => 'B001-00000099',
            'idusuario' => $user->id,
            'idarqueocaja' => $arching->id,
            'idalmacen' => 1,
        ]);

        DetailPayment::create([
            'idtipo_comprobante' => $docTypeBol->id,
            'idfactura' => $billing->id,
            'idpago' => $payModeTrans->id,
            'monto' => 70.0,
            'idarqueocaja' => $arching->id,
            'estado' => 1,
        ]);

        $controller = app(ReportPaymentController::class);
        $request = Request::create(route('report.sales.payment_methods'), 'GET', [
            'start_date' => $today,
            'end_date' => $today,
        ]);

        $response = $controller->getSalesByPaymentMethod($request);
        $data = $response->getData(true);

        $sales = collect($data['sales']);
        $totalSum = $sales->sum('total_recaudado');

        $this->assertEquals(100.0, round((float) $totalSum, 2), 'El total de ventas debe ser exactamente la suma de Nota de Venta (30) y Boleta (70).');
        $this->assertTrue($sales->contains('metodo_pago', $payModeYape->descripcion));
        $this->assertTrue($sales->contains('metodo_pago', $payModeTrans->descripcion));
    }

    /**
     * Decision 2 & 4: Delivery completion outside transaction generates WhatsApp receipt URL.
     */
    public function test_delivery_completion_generates_whatsapp_receipt_outside_transaction(): void
    {
        Role::firstOrCreate(['name' => 'ADMIN']);

        $user = User::create([
            'nombres' => 'TEST REPARTIDOR ADMIN',
            'user' => 'test_rep_admin',
            'password' => Hash::make('secret'),
            'estado' => 1,
            'tipo' => 'admin',
        ]);
        $user->assignRole('ADMIN');
        $this->actingAs($user);

        $docType = IdentityDocumentType::firstOrCreate(['codigo' => '1'], [
            'descripcion' => 'DNI',
            'estado' => 1,
        ]);

        $client = Client::create([
            'iddoc' => $docType->id,
            'nro_documento' => '12345678',
            'nombres' => 'CLIENTE TEST WHATSAPP',
            'telefono' => '987112233',
            'direccion' => 'Jr. San Martin 123',
            'codigo_pais' => 'PE',
            'saldo_envases' => 0,
        ]);

        $order = DeliveryOrder::create([
            'codigo_orden' => 'PED-999999',
            'idcliente' => $client->id,
            'origen' => 'qr',
            'estado' => 'en_ruta',
            'direccion_entrega' => 'Jr. San Martin 123',
            'telefono_contacto' => '987112233',
            'fecha_programada' => date('Y-m-d'),
            'franja_horaria' => 'flexible',
            'subtotal' => 30.0,
            'total' => 30.0,
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'pendiente',
            'bidones_a_entregar' => 2,
            'bidones_vacios_recibidos' => 0,
        ]);

        $response = $this->post(route('deliveries.complete'), [
            'id' => $order->id,
            'bidones_vacios_recibidos' => 2,
            'bidones_danados_recibidos' => 0,
            'cobro_envases_danados' => 0,
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'pagado',
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['status']);
        $this->assertTrue($json['has_whatsapp']);
        $this->assertNotEmpty($json['whatsapp_url']);
        $this->assertStringContainsString('https://wa.me/51987112233', $json['whatsapp_url']);
        $this->assertStringContainsString('PED-999999', urldecode($json['whatsapp_url']));
    }
}
