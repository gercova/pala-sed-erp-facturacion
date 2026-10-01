<?php

namespace Tests\Feature;

use App\Models\ArchingCash;
use App\Models\Billing;
use App\Models\Business;
use App\Models\Buy;
use App\Models\Cash;
use App\Models\Category;
use App\Models\Client;
use App\Models\Currency;
use App\Models\DeliveryOrder;
use App\Models\DetailPayment;
use App\Models\IdentityDocumentType;
use App\Models\InventoryAdjustment;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\SaleNote;
use App\Models\StockProduct;
use App\Models\TypeDocument;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Inventory\StockService;
use App\Services\Reports\FinancialReconciliationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockPurchasesAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehousePrincipal;

    protected Warehouse $warehouseSecundario;

    protected Product $physicalProduct;

    protected Product $serviceProduct;

    protected Client $provider;

    protected Client $customer;

    protected TypeDocument $docFactura;

    protected TypeDocument $docBoleta;

    protected TypeDocument $docNotaVenta;

    protected TypeDocument $docNotaCredito;

    protected TypeDocument $docNotaDebito;

    protected Currency $currency;

    protected PayMode $payModeEfectivo;

    protected PayMode $payModeYape;

    protected ArchingCash $archingCash;

    protected StockService $stockService;

    protected FinancialReconciliationService $reconciliationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);
        $this->reconciliationService = app(FinancialReconciliationService::class);

        Business::create([
            'idempresa' => 1,
            'nombre_comercial' => 'PALA SED ERP TEST',
            'razon_social' => 'PALA SED SAC',
            'ruc' => '20600000001',
            'direccion' => 'Av Principal 123',
            'codigo_pais' => 'PE',
            'ubigeo' => '150101',
            'modo_pago' => 1,
            'signo_moneda' => 'S/',
        ]);

        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->currency = Currency::where('codigo', 'PEN')->first() ?? Currency::first();

        $role = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);

        $permissions = [
            'report.sales.index',
            'report.sales.by_product.index',
            'report.payments.index',
            'admin.buys',
            'admin.providers',
        ];
        foreach ($permissions as $permName) {
            $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->warehousePrincipal = Warehouse::create([
            'descripcion' => 'Almacén Principal',
            'direccion' => 'Calle 1',
            'estado' => 1,
        ]);

        $this->warehouseSecundario = Warehouse::create([
            'descripcion' => 'Almacén Secundario',
            'direccion' => 'Calle 2',
            'estado' => 1,
        ]);

        $this->user = User::create([
            'nombres' => 'Admin User',
            'user' => 'admin_test',
            'password' => bcrypt('password123'),
            'tipo' => 'empleado',
            'idalmacen' => $this->warehousePrincipal->id,
            'estado' => 1,
        ]);
        $this->user->assignRole($role);

        $cash = Cash::create([
            'descripcion' => 'Caja Principal',
            'idalmacen' => $this->warehousePrincipal->id,
            'idusuario' => $this->user->id,
            'estado' => 1,
        ]);

        $this->archingCash = ArchingCash::create([
            'idcaja' => $cash->id,
            'idusuario' => $this->user->id,
            'idalmacen' => $this->warehousePrincipal->id,
            'fecha_inicio' => Carbon::today(),
            'hora_inicio' => '08:00:00',
            'monto_inicial' => 100.00,
            'total_ventas' => 0.00,
            'estado' => 1,
        ]);

        $unit = Unit::create(['codigo' => 'NIU', 'descripcion' => 'UNIDAD']);
        $category = Category::create(['descripcion' => 'AGUA Y BIDONES']);

        // Document types
        $this->docFactura = TypeDocument::firstOrCreate(['codigo' => '01'], ['descripcion' => 'FACTURA ELECTRONICA', 'estado' => 1]);
        $this->docBoleta = TypeDocument::firstOrCreate(['codigo' => '03'], ['descripcion' => 'BOLETA DE VENTA ELECTRONICA', 'estado' => 1]);
        $this->docNotaVenta = TypeDocument::firstOrCreate(['codigo' => '02'], ['descripcion' => 'NOTA DE VENTA', 'estado' => 1]);
        $this->docNotaCredito = TypeDocument::firstOrCreate(['codigo' => '07'], ['descripcion' => 'NOTA DE CREDITO ELECTRONICA', 'estado' => 1]);
        $this->docNotaDebito = TypeDocument::firstOrCreate(['codigo' => '08'], ['descripcion' => 'NOTA DE DEBITO ELECTRONICA', 'estado' => 1]);

        $this->payModeEfectivo = PayMode::firstOrCreate(['id' => 1], ['descripcion' => 'EFECTIVO']);
        $this->payModeYape = PayMode::firstOrCreate(['id' => 2], ['descripcion' => 'YAPE']);

        $docRuc = IdentityDocumentType::firstOrCreate(['codigo' => '6'], ['descripcion' => 'RUC', 'descripcion_documento' => 'RUC', 'estado' => 1]);
        $docDni = IdentityDocumentType::firstOrCreate(['codigo' => '1'], ['descripcion' => 'DNI', 'descripcion_documento' => 'DNI', 'estado' => 1]);

        $this->provider = Client::create([
            'iddoc' => $docRuc->id,
            'nro_documento' => '20123456789',
            'nombres' => 'PROVEEDOR INDUSTRIAL SAC',
            'direccion' => 'Av Industrial 456',
            'ubigeo' => '150101',
            'codigo_pais' => 'PE',
        ]);

        $this->customer = Client::create([
            'iddoc' => $docDni->id,
            'nro_documento' => '45678912',
            'nombres' => 'JUAN PEREZ',
            'direccion' => 'Calle Los Olivos 123',
            'ubigeo' => '150101',
            'codigo_pais' => 'PE',
        ]);

        // Physical product (opcion = 1)
        $this->physicalProduct = Product::create([
            'codigo_interno' => 'PROD-01',
            'descripcion' => 'BIDON DE AGUA 20L',
            'idcategoria' => $category->id,
            'idunidad' => $unit->id,
            'precio_compra' => 5.00,
            'precio_venta' => 15.00,
            'opcion' => 1,
            'igv' => 18,
            'stock_actual' => 0,
        ]);

        // Service product (opcion = 2)
        $this->serviceProduct = Product::create([
            'codigo_interno' => 'SERV-01',
            'descripcion' => 'SERVICIO DE MANTENIMIENTO DISPENSADOR',
            'idcategoria' => $category->id,
            'idunidad' => $unit->id,
            'precio_compra' => 0.00,
            'precio_venta' => 30.00,
            'opcion' => 2,
            'igv' => 18,
            'stock_actual' => 0,
        ]);
    }

    public function test_stock_products_is_source_of_truth_and_products_stock_actual_is_derived_sum(): void
    {
        // 1. Initial stock should be zero
        $this->assertEquals(0, $this->physicalProduct->fresh()->stock_actual);

        // 2. Increase stock in warehousePrincipal
        $this->stockService->increaseStock(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            quantity: 50,
            movementType: 'entrada_inicial'
        );

        // Verify stock_products for warehousePrincipal has 50
        $stockW1 = StockProduct::where('idproducto', $this->physicalProduct->id)
            ->where('idalmacen', $this->warehousePrincipal->id)
            ->first();
        $this->assertNotNull($stockW1);
        $this->assertEquals(50, $stockW1->stock_actual);

        // Verify products.stock_actual is automatically synced to 50
        $this->assertEquals(50, $this->physicalProduct->fresh()->stock_actual);

        // 3. Increase stock in warehouseSecundario
        $this->stockService->increaseStock(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehouseSecundario->id,
            quantity: 30,
            movementType: 'compra'
        );

        $stockW2 = StockProduct::where('idproducto', $this->physicalProduct->id)
            ->where('idalmacen', $this->warehouseSecundario->id)
            ->first();
        $this->assertEquals(30, $stockW2->stock_actual);

        // Total products.stock_actual must be derived sum = 50 + 30 = 80
        $this->assertEquals(80, $this->physicalProduct->fresh()->stock_actual);

        // 4. Verification query: SUM(stock_products.stock_actual) = products.stock_actual
        $sumDb = StockProduct::where('idproducto', $this->physicalProduct->id)->sum('stock_actual');
        $this->assertEquals($sumDb, $this->physicalProduct->fresh()->stock_actual);
    }

    public function test_services_do_not_affect_stock(): void
    {
        // Service product has opcion = 2
        $result = $this->stockService->increaseStock(
            productId: $this->serviceProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            quantity: 10
        );

        $this->assertNull($result);
        $this->assertDatabaseMissing('stock_products', [
            'idproducto' => $this->serviceProduct->id,
        ]);
        $this->assertEquals(0, $this->serviceProduct->fresh()->stock_actual);
    }

    public function test_inventory_adjustments_are_recorded_and_reflected_in_kardex(): void
    {
        // Set initial stock to 20
        $this->stockService->increaseStock(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            quantity: 20
        );

        // Perform adjustment to 15 (difference = -5)
        $adjustment = $this->stockService->adjustStock(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            newStock: 15,
            reason: 'Merma por bidones dañados en almacén',
            adjustmentType: 'merma',
            userId: $this->user->id,
            referenceDocument: 'INF-001'
        );

        $this->assertInstanceOf(InventoryAdjustment::class, $adjustment);
        $this->assertEquals(20, $adjustment->stock_anterior);
        $this->assertEquals(15, $adjustment->stock_nuevo);
        $this->assertEquals(-5, $adjustment->cantidad_diferencia);
        $this->assertEquals(15, $this->physicalProduct->fresh()->stock_actual);

        // Check audit table
        $this->assertDatabaseHas('inventory_adjustments', [
            'id' => $adjustment->id,
            'motivo' => 'Merma por bidones dañados en almacén',
        ]);
    }

    public function test_stock_service_transfer_stock_between_warehouses(): void
    {
        // Seed warehouse 1 with 40 units
        $this->stockService->increaseStock(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            quantity: 40
        );

        // Transfer 15 units from warehouse 1 to warehouse 2
        $this->stockService->transferStock(
            productId: $this->physicalProduct->id,
            fromWarehouseId: $this->warehousePrincipal->id,
            toWarehouseId: $this->warehouseSecundario->id,
            quantity: 15,
            documentNumber: 'TR-0001',
            userId: $this->user->id
        );

        $stockW1 = StockProduct::where('idproducto', $this->physicalProduct->id)
            ->where('idalmacen', $this->warehousePrincipal->id)
            ->first();
        $stockW2 = StockProduct::where('idproducto', $this->physicalProduct->id)
            ->where('idalmacen', $this->warehouseSecundario->id)
            ->first();

        $this->assertEquals(25, $stockW1->stock_actual);
        $this->assertEquals(15, $stockW2->stock_actual);
        $this->assertEquals(40, $this->physicalProduct->fresh()->stock_actual);
    }

    public function test_stock_reconcile_artisan_command_repairs_desynchronized_products(): void
    {
        $this->stockService->increaseStock(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            quantity: 50
        );

        // Artificially corrupt direct products.stock_actual
        DB::table('products')->where('id', $this->physicalProduct->id)->update(['stock_actual' => 999]);
        $this->assertEquals(999, Product::find($this->physicalProduct->id)->stock_actual);

        // Run artisan reconciliation command
        $exitCode = Artisan::call('stock:reconcile');
        $this->assertEquals(0, $exitCode);

        // Verify products.stock_actual is restored to 50
        $this->assertEquals(50, Product::find($this->physicalProduct->id)->stock_actual);
    }

    public function test_purchases_prevent_duplicate_documents_and_record_last_purchase_cost(): void
    {
        $this->actingAs($this->user);

        // Create initial buy
        Buy::create([
            'idtipo_comprobante' => $this->docFactura->id,
            'serie' => 'F001',
            'correlativo' => '00000100',
            'fecha_emision' => Carbon::today(),
            'fecha_vencimiento' => Carbon::today(),
            'hora' => '10:00:00',
            'idproveedor' => $this->provider->id,
            'idmoneda' => $this->currency->id,
            'idpago' => 1,
            'modo_pago' => 1,
            'anticipo' => 0,
            'igv' => 18.00,
            'gratuita' => 0,
            'otros_cargos' => 0,
            'total' => 118.00,
            'estado' => 1,
            'idusuario' => $this->user->id,
        ]);

        // Try to save duplicate purchase via BuyController save
        $response = $this->postJson('/buys/save', [
            'idtipo_comprobante' => $this->docFactura->id,
            'serie' => 'F001',
            'correlativo' => '00000100',
            'fecha_emision' => Carbon::today()->toDateString(),
            'fecha_vencimiento' => Carbon::today()->toDateString(),
            'dni_ruc' => $this->provider->id,
            'modo_pago' => $this->payModeEfectivo->id,
        ]);

        $response->assertStatus(422);

        // Verify recordPurchase updates cost and stock
        $this->stockService->recordPurchase(
            productId: $this->physicalProduct->id,
            warehouseId: $this->warehousePrincipal->id,
            quantity: 20,
            purchaseCost: 6.50,
            documentNumber: 'F001-00000100',
            userId: $this->user->id
        );

        $stock = StockProduct::where('idproducto', $this->physicalProduct->id)
            ->where('idalmacen', $this->warehousePrincipal->id)
            ->first();

        $this->assertEquals(20, $stock->stock_actual);
        $this->assertEquals(6.50, $stock->precio_compra);
        $this->assertEquals(6.50, $this->physicalProduct->fresh()->precio_compra);
    }

    public function test_supplier_prevents_duplicates_by_document_and_type(): void
    {
        $this->actingAs($this->user);

        // Duplicate with same iddoc and nro_documento
        $response = $this->postJson('/providers/save', [
            'tipo_documento' => $this->provider->iddoc,
            'dni_ruc' => $this->provider->nro_documento,
            'razon_social' => 'PROVEEDOR REPETIDO SAC',
            'direccion' => 'Av Otra 999',
            'departamento' => '15',
            'provincia' => '1501',
            'distrito' => '150101',
        ]);

        $response->assertStatus(422);
    }

    public function test_financial_reconciliation_verifies_equality_total_reports_equals_total_cash_equals_total_docs_plus_notes(): void
    {
        $testDate = Carbon::today()->toDateString();

        // 1. Regular Sale Note (NV 1) = S/ 50.00 en Efectivo
        $saleNote1 = SaleNote::create([
            'idtipo_comprobante' => $this->docNotaVenta->id,
            'serie' => 'NV01',
            'correlativo' => '00000001',
            'fecha_emision' => $testDate,
            'fecha_vencimiento' => $testDate,
            'hora' => '09:00:00',
            'idcliente' => $this->customer->id,
            'modo_pago' => 1,
            'subtotal' => 42.37,
            'igv' => 7.63,
            'total' => 50.00,
            'estado' => 1,
            'idusuario' => $this->user->id,
            'idarqueocaja' => $this->archingCash->id,
        ]);
        DetailPayment::create([
            'idtipo_comprobante' => $this->docNotaVenta->id,
            'idfactura' => $saleNote1->id,
            'idpago' => $this->payModeEfectivo->id,
            'monto' => 50.00,
            'idarqueocaja' => $this->archingCash->id,
            'estado' => 1,
        ]);

        // 2. Factura electrónica (F001-1) = S/ 100.00 en Efectivo
        $billing1 = Billing::create([
            'idtipo_comprobante' => $this->docFactura->id,
            'serie' => 'F001',
            'correlativo' => '00000001',
            'fecha_emision' => $testDate,
            'fecha_vencimiento' => $testDate,
            'hora' => '10:00:00',
            'idcliente' => $this->customer->id,
            'idmoneda' => $this->currency->id,
            'idpago' => $this->payModeEfectivo->id,
            'modo_pago' => 1,
            'gravada' => 84.75,
            'igv' => 15.25,
            'total' => 100.00,
            'anulado' => false,
            'idusuario' => $this->user->id,
            'idarqueocaja' => $this->archingCash->id,
            'idalmacen' => $this->warehousePrincipal->id,
        ]);
        DetailPayment::create([
            'idtipo_comprobante' => $this->docFactura->id,
            'idfactura' => $billing1->id,
            'idpago' => $this->payModeEfectivo->id,
            'monto' => 100.00,
            'idarqueocaja' => $this->archingCash->id,
            'estado' => 1,
        ]);

        // 3. Boleta electrónica (B001-1) = S/ 80.00 en Yape (digital)
        $billing2 = Billing::create([
            'idtipo_comprobante' => $this->docBoleta->id,
            'serie' => 'B001',
            'correlativo' => '00000001',
            'fecha_emision' => $testDate,
            'fecha_vencimiento' => $testDate,
            'hora' => '11:00:00',
            'idcliente' => $this->customer->id,
            'idmoneda' => $this->currency->id,
            'idpago' => $this->payModeYape->id,
            'modo_pago' => 1,
            'gravada' => 67.80,
            'igv' => 12.20,
            'total' => 80.00,
            'anulado' => false,
            'idusuario' => $this->user->id,
            'idarqueocaja' => $this->archingCash->id,
            'idalmacen' => $this->warehousePrincipal->id,
        ]);
        DetailPayment::create([
            'idtipo_comprobante' => $this->docBoleta->id,
            'idfactura' => $billing2->id,
            'idpago' => $this->payModeYape->id,
            'monto' => 80.00,
            'idarqueocaja' => $this->archingCash->id,
            'estado' => 1,
        ]);

        // 4. Delivery Order converted to formal document:
        // Initially SaleNote NV 2 = S/ 30.00, then converted to Boleta B001-2 = S/ 30.00
        $saleNoteConverted = SaleNote::create([
            'idtipo_comprobante' => $this->docNotaVenta->id,
            'serie' => 'NV01',
            'correlativo' => '00000002',
            'fecha_emision' => $testDate,
            'fecha_vencimiento' => $testDate,
            'hora' => '12:00:00',
            'idcliente' => $this->customer->id,
            'modo_pago' => 1,
            'subtotal' => 25.42,
            'igv' => 4.58,
            'total' => 30.00,
            'estado' => 1,
            'idusuario' => $this->user->id,
            'idarqueocaja' => $this->archingCash->id,
        ]);
        $billingConverted = Billing::create([
            'idtipo_comprobante' => $this->docBoleta->id,
            'serie' => 'B001',
            'correlativo' => '00000002',
            'fecha_emision' => $testDate,
            'fecha_vencimiento' => $testDate,
            'hora' => '12:30:00',
            'idcliente' => $this->customer->id,
            'idmoneda' => $this->currency->id,
            'idpago' => $this->payModeEfectivo->id,
            'modo_pago' => 1,
            'gravada' => 25.42,
            'igv' => 4.58,
            'total' => 30.00,
            'anulado' => false,
            'idusuario' => $this->user->id,
            'idarqueocaja' => $this->archingCash->id,
            'idalmacen' => $this->warehousePrincipal->id,
        ]);

        // Delivery order has BOTH idnotaventa and idfactura
        DeliveryOrder::create([
            'codigo_orden' => 'ORD-TEST-001',
            'idcliente' => $this->customer->id,
            'idnotaventa' => $saleNoteConverted->id,
            'idfactura' => $billingConverted->id,
            'origen' => 'pos',
            'estado' => 'ENTREGADO',
            'direccion_entrega' => 'Calle 123',
            'fecha_programada' => $testDate,
            'subtotal' => 25.42,
            'total' => 30.00,
            'estado_pago' => 'pagado',
            'idalmacen' => $this->warehousePrincipal->id,
        ]);

        // Payment for the formal document
        DetailPayment::create([
            'idtipo_comprobante' => $this->docBoleta->id,
            'idfactura' => $billingConverted->id,
            'idpago' => $this->payModeEfectivo->id,
            'monto' => 30.00,
            'idarqueocaja' => $this->archingCash->id,
            'estado' => 1,
        ]);

        // 5. Nota de Crédito (NC01-1) de S/ 20.00 sobre la Factura 1 (debe restar)
        $creditNote = Billing::create([
            'idtipo_comprobante' => $this->docNotaCredito->id,
            'serie' => 'FC01',
            'correlativo' => '00000001',
            'fecha_emision' => $testDate,
            'fecha_vencimiento' => $testDate,
            'hora' => '13:00:00',
            'idcliente' => $this->customer->id,
            'idmoneda' => $this->currency->id,
            'idpago' => $this->payModeEfectivo->id,
            'modo_pago' => 1,
            'gravada' => 16.95,
            'igv' => 3.05,
            'total' => 20.00,
            'anulado' => false,
            'idusuario' => $this->user->id,
            'idarqueocaja' => $this->archingCash->id,
            'idalmacen' => $this->warehousePrincipal->id,
        ]);
        DetailPayment::create([
            'idtipo_comprobante' => $this->docNotaCredito->id,
            'idfactura' => $creditNote->id,
            'idpago' => $this->payModeEfectivo->id,
            'monto' => 20.00,
            'idarqueocaja' => $this->archingCash->id,
            'estado' => 1,
        ]);

        // EXPECTED TOTALS:
        // SaleNotes (valid and not converted): NV 1 = 50.00 (NV 2 excluded!)
        // Billings: Factura 1 (100) + Boleta 1 (80) + Boleta 2 (30) - NC 1 (20) = 190.00
        // Total Sales = 50.00 + 190.00 = 240.00
        // Total Payments = Efectivo (50 + 100 + 30 - 20 = 160) + Yape (80) = 240.00

        $reconciliation = $this->reconciliationService->reconcileDay($testDate);

        $this->assertEquals(240.00, $reconciliation['total_reports']);
        $this->assertEquals(240.00, $reconciliation['total_payments']);
        $this->assertEquals(160.00, $reconciliation['total_cash']);
        $this->assertEquals(80.00, $reconciliation['total_digital']);
        $this->assertEquals(190.00, $reconciliation['total_documents_net']);
        $this->assertEquals(50.00, $reconciliation['total_sale_notes']);
        $this->assertEquals(240.00, $reconciliation['total_documents_plus_notes']);
        $this->assertEquals(1, $reconciliation['converted_notes_excluded_count']);
        $this->assertEquals(30.00, $reconciliation['converted_notes_excluded_amount']);
        $this->assertTrue($reconciliation['is_balanced']);
        $this->assertEquals(0.00, $reconciliation['discrepancy']);

        // Test API Endpoint for Reconciliation
        $this->actingAs($this->user);
        $response = $this->getJson('/reports/sales/reconciliation?date='.$testDate);
        $response->assertStatus(200);
        $response->assertJsonPath('reconciliation.is_balanced', true);
        $this->assertEquals(240.00, (float) $response->json('reconciliation.total_reports'));
    }
}
