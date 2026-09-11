<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchingCashController; 
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BillingReportController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\PayModeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleNoteController;
use App\Http\Controllers\SerieController;
use App\Http\Controllers\TransferOrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportPaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseSelectorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportSalesController;
use App\Http\Controllers\ShipmentGuideController;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/'                              , [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::controller(LoginController::class)->prefix('login')->group(function() {
    Route::post('/login'                    , 'login')->name('login.login');
    Route::get('/logout'                    , 'logout')->name('login.logout');
});

Route::get('/home'                          , [HomeController::class, 'index'])->name('admin.home')->middleware(['auth', 'can:admin.home']);
Route::controller(WarehouseSelectorController::class)->prefix('establishment')->middleware('auth')->group(function() {
    Route::get('/'                          , 'index')->name('warehouse.selector.index');
    Route::post('/select'                   , 'store')->name('warehouse.selector.store');
});
Route::get('/home/ventas-mensuales'         , [HomeController::class, 'ventasMensuales'])->name('home.ventas_mensuales')->middleware(['auth', 'can:admin.home']);
Route::get('/home/reporte-ingresos'         , [HomeController::class, 'reporteIngresos'])->name('home.reporte_ingresos')->middleware(['auth', 'can:admin.home']);
Route::get('/home/metodo-pagos'             , [HomeController::class, 'metodosPagoVentas'])->name('home.metodo_pagos')->middleware(['auth', 'can:admin.home']);
 
Route::controller(Businesscontroller::class)->prefix('business')->middleware(['auth', 'can:admin.business'])->group(function() {
    Route::get('/'                          , 'index')->name('admin.business');
    Route::post('/load-ubigeo'              , 'load_ubigeo')->name('admin.load_ubigeo');
    Route::post('/load-provinces'           , 'load_provinces')->name('admin.load_provinces');
    Route::post('/load-districts'           , 'load_districts')->name('admin.load_districts');
    Route::post('/load-logo'                , 'load_logo')->name('business.load_logo');
    Route::post('/save-info'                , 'save_info')->name('business.save_info');
    Route::post('/save-sunat'               , 'save_sunat')->name('business.save_sunat');
});

Route::controller(CashController::class)->prefix('cashes')->middleware(['auth', 'can:admin.cashes'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.cashes');
    Route::get('/get'                 , 'get')->name('cashes.get');
    Route::post('/save'               , 'save')->name('cashes.save');
    Route::post('/detail'             , 'detail')->name('cashes.detail');
    Route::post('/store'              , 'store')->name('cashes.store');
    Route::post('/delete'             , 'delete')->name('cashes.delete');
});

Route::controller(PayModeController::class)->prefix('pay-modes')->middleware(['auth', 'can:admin.paymodes'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.paymodes');
    Route::get('/get'                 , 'get')->name('paymodes.get');
    Route::post('/save'               , 'save')->name('paymodes.save');
    Route::post('/detail'             , 'detail')->name('paymodes.detail');
    Route::post('/store'              , 'store')->name('paymodes.store');
    Route::post('/delete'             , 'delete')->name('paymodes.delete');
});

Route::controller(SerieController::class)->prefix('series')->middleware(['auth', 'can:admin.series'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.series');
    Route::get('/get'                 , 'get')->name('series.get');
    Route::post('/save'               , 'save')->name('series.save');
    Route::post('/detail'             , 'detail')->name('series.detail');
    Route::post('/store'              , 'store')->name('series.store');
    Route::post('/delete'             , 'delete')->name('series.delete');
});

Route::controller(CountryController::class)->prefix('countries')->group(function() {
    Route::get('/'                    , 'index')->name('admin.countries')->middleware('auth');
    Route::get('/get'                 , 'get')->name('countries.get');
    Route::post('/save'               , 'save')->name('countries.save');
    Route::post('/detail'             , 'detail')->name('countries.detail');
    Route::post('/store'              , 'store')->name('countries.store');
    Route::post('/delete'             , 'delete')->name('countries.delete');
});

Route::controller(ClientController::class)->prefix('clients')->middleware(['auth', 'can:admin.clients'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.clients');
    Route::get('/get'                 , 'get')->name('clients.get');
    Route::post('/search-document'    , 'searchDocument')->name('admin.search_client_document_record');
    Route::post('/save'               , 'save')->name('clients.save');
    Route::post('/detail'             , 'detail')->name('clients.detail');
    Route::post('/store'              , 'store')->name('clients.store');
    Route::post('/delete'             , 'delete')->name('clients.delete');
});

Route::controller(ProviderController::class)->prefix('providers')->middleware(['auth', 'can:admin.providers'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.providers');
    Route::get('/get'                 , 'get')->name('providers.get');
    Route::post('/search-document'    , 'searchDocument')->name('admin.search_provider_document_record');
    Route::post('/save'               , 'save')->name('providers.save');
    Route::post('/detail'             , 'detail')->name('providers.detail');
    Route::post('/store'              , 'store')->name('providers.store');
    Route::post('/delete'             , 'delete')->name('providers.delete');
});

Route::controller(CategoryController::class)->prefix('categories')->middleware(['auth', 'can:admin.categories'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.categories');
    Route::get('/get'                 , 'get')->name('categories.get');
    Route::post('/save'               , 'save')->name('categories.save');
    Route::post('/detail'             , 'detail')->name('categories.detail');
    Route::post('/store'              , 'store')->name('categories.store');
    Route::post('/delete'             , 'delete')->name('categories.delete');
});

Route::controller(ProductController::class)->prefix('products')->middleware(['auth', 'can:admin.products'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.products');
    Route::get('/get'                 , 'get')->name('products.get');
    Route::post('/save'               , 'save')->name('products.save');
    Route::post('/detail'             , 'detail')->name('products.detail');
    Route::post('/store'              , 'store')->name('products.store');
    Route::post('/delete'             , 'delete')->name('products.delete');
    Route::post('/upload-excel'       , 'upload')->name('products.upload_excel');
    Route::get('/download-excel'      , 'download')->name('products.download_excel');
    Route::post('/view-detail'        , 'view_detail')->name('products.view_detail');
});

Route::controller(WarehouseController::class)->prefix('warehouses')->middleware(['auth', 'can:admin.warehouses'])->group(function() {
    Route::get('/'                      , 'index')->name('admin.warehouses');
    Route::get('/get'                   , 'get')->name('warehouses.get');
    Route::post('/save'                 , 'save')->name('warehouses.save')->middleware('auth');
    Route::post('/detail'               , 'detail')->name('warehouses.detail');
    Route::post('/store'                , 'store')->name('warehouses.store');
    Route::post('/delete'               , 'delete')->name('warehouses.delete');
    Route::get('/{id}'                  , 'products')->name('admin.products_warehouse');
    Route::post('/list'                 , 'list_products')->name('admin.list_products_warehouse');
    Route::post('/get-prod-warehouse'   , 'get_prod_warehouse')->name('admin.get_product_warehouse');
    Route::post('/get-detail'           , 'get_detail_products')->name('admin.get_detail_products');
    Route::post('/save-product'         , 'save_product_stock')->name('admin.save_product_stock');
    Route::post('/store-product'        , 'store_product_stocks')->name('admin.store_product_stocks');
    Route::post('/save-products-all'    , 'save_products_all')->name('admin.save_products_stock_all');
    Route::post('/detail-stock'         , 'detail_stock')->name('admin.detail_stock_product');
    Route::post('/detail-sum'           , 'detail_sum')->name('admin.detail_sum_product');
    Route::post('/store-stock'          , 'store_stock')->name('admin.store_stock_product');
    Route::post('/sum-stock'            , 'sum_stock')->name('admin.sum_stock_product');
    Route::post('/barcode_sum'          , 'barcode_sum')->name('admin.barcode_sum_product');
    Route::post('/delete-stock'         , 'delete_stock')->name('admin.delete_stock_product');
    Route::get('/export-products/{id}'  , 'export_products')->name('admin.export_products_warehouse');
    Route::post('/upload-excel'         , 'upload_excel_products')->name('admin.upload_excel_warehouse');
});

Route::controller(TransferOrderController::class)->prefix('transferorders')->middleware(['auth', 'can:admin.transfer_orders'])->group(function() {
    Route::get('/'                           , 'index')->name('admin.transfer_orders');
    Route::get('/create'                     , 'create')->name('admin.create_transfer_order');
    Route::get('/get-transfer-orders'        , 'get')->name('admin.get_transfer_orders');
    Route::post('/load-serie-transfer'       , 'load_serie')->name('admin.load_serie_transfer');
    Route::post('/load-warehouse-office'     , 'load_warehouse_office')->name('admin.load_warehouse_office');
    Route::post('/load-warehouse-dispatch'   , 'load_warehouse_dispatch')->name('admin.load_warehouse_dispatch');
    Route::post('/load-cart-transfer'        , 'load_cart')->name('admin.load_cart_transfer');
    Route::post('/search-product-transfer'   , 'search_product')->name('admin.search_product_transfer');
    Route::post('/detail-product-transfer'   , 'detail_product')->name('admin.detail_product_transfer');
    Route::post('/add-product-transfer'      , 'add_product')->name('admin.add_product_transfer');
    Route::post('/delete-product-transfer'   , 'delete_product')->name('admin.delete_product_transfer');
    Route::post('/store-product-transfer'    , 'store_product')->name('admin.store_product_transfer');
    Route::post('/save-transfer'             , 'save')->name('admin.save_transfer');
    Route::post('/detail-transfer'           , 'detail')->name('admin.detail_transfer');
    Route::post('/anulled-transfer'          , 'anulled')->name('admin.anulled_tansfer_order');
    Route::post('/move-transfer'             , 'move')->name('admin.move_transfer');
    Route::post('/print-transfer'            , 'print')->name('admin.print_transfer');
    Route::get('/pdf'                        , 'test_pdf');
});

Route::controller(KardexController::class)->prefix('kardex')->middleware(['auth', 'can:admin.products'])->group(function() {
    Route::get('/'                           , 'index')->name('admin.kardex');
});

Route::controller(QuoteController::class)->prefix('quotes')->middleware(['auth', 'can:admin.quotes'])->group(function() {
    Route::get('/'                          , 'index')->name('admin.quotes');
    Route::get('/get'                       , 'get')->name('quotes.get');
    Route::get('/create'                    , 'create')->name('admin.create_quote');
    Route::get('/q{id}'                      , 'edit')->name('admin.edit_quote');
    Route::post('/detail'                   , 'detail')->name('quotes.detail');
    Route::get('/q{id}/convert-sale'        , 'convert_to_sale')->name('admin.convert_quote_to_sale');
    Route::post('/save'                     , 'save')->name('admin.save_quote');
    Route::post('/load-clients'             , 'load_clients')->name('admin.load_clients');
    Route::post('/load-cart'                , 'load_cart')->name('admin.load_cart_quotes');
    Route::post('/add-product'              , 'add_product')->name('admin.add_product_quote');
    Route::post('/delete-product'           , 'delete_product')->name('admin.delete_product_quote');
    Route::post('/store-product'            , 'store_product')->name('admin.store_product_quote');
    Route::post('/get-product'              , 'get_product_update')->name('admin.get_product_quote_update');
    Route::post('/store-product-update'     , 'store_product_update')->name('admin.store_product_quote_update');
    Route::post('/get-product-warehouse'    , 'get_product_idwarehouse')->name('admin.get_products_by_idwarehouse');
    Route::post('/gen-quote'                , 'gen_update')->name('admin.gen_quote_update');
    Route::post('/print-quote'              , 'print')->name('admin.print_quote');
    Route::post('/print-a4'                 , 'print_a4')->name('admin.print_quote_a4');
    Route::post('/print-ticket'             , 'print_ticket')->name('admin.print_quote_ticket');
    Route::post('/send-mail'                , 'send_mail')->name('admin.send_mail_quote');
    Route::get('/pdf'                       , 'test_pdf');
    Route::post('/get-price'                , 'get_product')->name('admin.get_product_buy_quote');
});

Route::controller(PosController::class)->prefix('pos')->middleware(['auth', 'can:admin.pos'])->group(function() {
    Route::get('/'                     , 'index')->name('admin.pos');
    Route::get('/crear'               , 'create')->name('admin.pos.create');
    Route::get('/get'                 , 'get')->name('admin.pos.get');
    Route::post('/search-product'     , 'search_product')->name('admin.search_product_pos');
    Route::post('/add-product-search' , 'add_product_search')->name('admin.add_product_search');
    Route::post('/load-cart'          , 'load_cart')->name('admin.load_cart_pos');
    Route::post('/add-product'        , 'add_product')->name('admin.add_product_pos');
    Route::post('/add-product-barcode', 'add_product_barcode')->name('admin.add_product_barcode');
    Route::post('/delete-product'     , 'delete_product')->name('admin.delete_product_pos');
    Route::post('/clear-cart'         , 'clear_cart')->name('admin.clear_cart_pos');
    Route::post('/store-product'      , 'store_product')->name('admin.store_product_pos');
    Route::post('/open-modal'         , 'open_modal')->name('pos.open_modal_confirm');
    Route::post('/save-sale'          , 'save_sale')->name('pos.save_sale');
});

Route::controller(SaleNoteController::class)->prefix('salenotes')->middleware(['auth', 'can:admin.sale_notes'])->group(function() {
    Route::get('/'                          , 'index')->name('admin.sale_notes');
    Route::get('/get'                       , 'get')->name('sale_notes.get');
    Route::post('/print-ticket'             , 'print_ticket')->name('admin.print_sale_note');
    Route::post('/print-a4'                 , 'print_a4')->name('admin.print_sale_note_a4');
    Route::post('/send-mail'                , 'send_mail')->name('admin.send_mail_sale_note');
    Route::post('/anulled'                  , 'anulled')->name('admin.anulled_sale_note');
});

Route::controller(BillingController::class)->prefix('billings')->middleware(['auth', 'can:admin.billings'])->group(function() {
    Route::get('/'                          , 'index')->name('admin.billings');
    Route::get('/get'                       , 'get')->name('billings.get');
    Route::get('/credit-notes'              , 'credit_notes_index')->name('admin.billing_credit_notes');
    Route::get('/credit-notes/get'          , 'get_credit_notes')->name('billings.credit_notes.get');
    Route::get('/debit-notes'               , 'debit_notes_index')->name('admin.billing_debit_notes');
    Route::get('/debit-notes/get'           , 'get_debit_notes')->name('billings.debit_notes.get');
    Route::post('/{id}/credit-note'         , 'create_credit_note')->name('admin.create_credit_note_billing');
    Route::post('/{id}/debit-note'          , 'create_debit_note')->name('admin.create_debit_note_billing');
    Route::post('/print-ticket'             , 'print_ticket')->name('admin.print_billing_ticket');
    Route::post('/print-a4'                 , 'print_a4')->name('admin.print_billing_a4');
    Route::get('/{id}/xml'                  , 'download_xml')->name('admin.billing_xml');
    Route::get('/{id}/cdr'                  , 'download_cdr')->name('admin.billing_cdr');
    Route::post('/{id}/dispatch'            , 'dispatch')->name('admin.dispatch_billing');
});

Route::controller(ShipmentGuideController::class)->prefix('shipment-guides')->middleware(['auth', 'can:admin.shipment_guides'])->group(function() {
    Route::get('/'                          , 'index')->name('admin.shipment_guides');
    Route::get('/create'                    , 'create')->name('admin.create_shipment_guide');
    Route::get('/get'                       , 'get')->name('shipment_guides.get');
    Route::get('/search-ubigeo'             , 'searchUbigeo')->name('admin.search_shipment_guide_ubigeo');
    Route::post('/save'                     , 'save')->name('admin.save_shipment_guide');
    Route::post('/detail'                   , 'detail')->name('admin.detail_shipment_guide');
    Route::post('/print-ticket'             , 'print_ticket')->name('admin.print_shipment_guide_ticket');
    Route::post('/print-a4'                 , 'print_a4')->name('admin.print_shipment_guide_a4');
});

Route::controller(BillingReportController::class)->prefix('billings/reports')->middleware(['auth'])->group(function() {
    Route::get('/sales-register', 'salesRegister')->name('report.billings.sales_register')->middleware('can:report.billings.sales_register');
    Route::get('/sales-register/data', 'getSalesRegister')->name('report.billings.sales_register.data')->middleware('can:report.billings.sales_register');
    Route::get('/sales-register/pdf', 'salesRegisterPdf')->name('report.billings.sales_register.pdf')->middleware('can:report.billings.sales_register');
    Route::get('/sales-register/excel', 'salesRegisterExcel')->name('report.billings.sales_register.excel')->middleware('can:report.billings.sales_register');

    Route::get('/billing-documents', 'billingDocuments')->name('report.billings.billing_documents')->middleware('can:report.billings.billing_documents');
    Route::get('/billing-documents/data', 'getBillingDocuments')->name('report.billings.billing_documents.data')->middleware('can:report.billings.billing_documents');
    Route::get('/billing-documents/pdf', 'billingDocumentsPdf')->name('report.billings.billing_documents.pdf')->middleware('can:report.billings.billing_documents');
    Route::get('/billing-documents/excel', 'billingDocumentsExcel')->name('report.billings.billing_documents.excel')->middleware('can:report.billings.billing_documents');

    Route::get('/credit-notes', 'creditNotes')->name('report.billings.credit_notes')->middleware('can:report.billings.credit_notes');
    Route::get('/credit-notes/data', 'getCreditNotes')->name('report.billings.credit_notes.data')->middleware('can:report.billings.credit_notes');
    Route::get('/credit-notes/pdf', 'creditNotesPdf')->name('report.billings.credit_notes.pdf')->middleware('can:report.billings.credit_notes');
    Route::get('/credit-notes/excel', 'creditNotesExcel')->name('report.billings.credit_notes.excel')->middleware('can:report.billings.credit_notes');
});

Route::controller(ArchingCashController::class)->prefix('archingcash')->middleware(['auth', 'can:admin.arching_cashes'])->group(function() {
    Route::get('/'                    , 'index')->name('admin.arching_cashes');
    Route::get('/get'                 , 'get')->name('arching_cashes.get');
    Route::post('/save'               , 'save')->name('arching_cash.save');
    Route::post('/store'              , 'store')->name('arching_cash.store');
    Route::post('/delete'             , 'delete')->name('arching_cash.delete');
    Route::post('/close'              , 'close')->name('admin.close_cash');
    Route::post('/detail-cash'        , 'detail_cash')->name('admin.get_detail_cash');
    Route::post('/detail-cashes'      , 'detail_cashes')->name('admin.get_detail_cashes');
    Route::post('/get-summary'        , 'get_summary')->name('admin.get_summary');
    Route::post('/save-deposit'       , 'save_deposit')->name('admin.save_deposit');
    Route::post('/save-withdrawal'    , 'save_withdrawal')->name('admin.save_withdrawal');
    Route::post('/print-resumen'      , 'print_resumen')->name('admin.print_resumen_archingcash');
    Route::post('/print-summary'      , 'print_summary')->name('admin.print_summary');
});

Route::controller(UserController::class)->prefix('users')->middleware(['auth', 'can:admin.users'])->group(function() {
    Route::get('/'                              , 'index')->name('admin.users');
    Route::get('/get-users'                     , 'get')->name('users.get');
    Route::post('/save-user'                    , 'save')->name('users.save');
    Route::post('/detail-user'                  , 'detail')->name('users.detail');
    Route::post('/store-user'                   , 'store')->name('users.store');
    Route::post('/delete-user'                  , 'delete')->name('users.delete');
    Route::post('/view-role'                    , 'view_role')->name('users.view_role');
    Route::post('/update-role'                  , 'update')->name('users.update_role');
});

Route::controller(RoleController::class)->prefix('roles')->middleware(['auth', 'can:admin.roles'])->group(function() {
    Route::get('/'                              , 'index')->name('admin.roles');
    Route::get('/get-roles'                     , 'get')->name('roles.get');
    Route::post('/save-role'                    , 'save')->name('roles.save');
    Route::post('/detail-role'                  , 'detail')->name('roles.detail');
    Route::post('/store-role'                   , 'store')->name('roles.store');
    Route::post('/delete-role'                  , 'delete')->name('roles.delete');
});

# Reportes
Route::get('/reportes/ventas',                  [ReportSalesController::class, 'index'])->name('report.sales.index')->middleware(['auth', 'can:report.sales.index']);
Route::get('/reportes/ventas/data',             [ReportSalesController::class, 'getSalesReport'])->name('report.sales.data')->middleware(['auth', 'can:report.sales.index']);
Route::get('/by-product',                       [ReportSalesController::class, 'salesByProductIndex'])->name('report.sales.by_product.index')->middleware(['auth', 'can:report.sales.by_product.index']);
Route::get('/reports/sales/products',           [ReportSalesController::class, 'getSalesByProduct'])->name('report.sales.products')->middleware(['auth', 'can:report.sales.by_product.index']);


Route::get('/reportes/pagos',                   [ReportPaymentController::class, 'index'])->name('report.payments.index')->middleware(['auth', 'can:report.payments.index']);
Route::get('/reports/sales/payment-methods'     , [ReportPaymentController::class, 'getSalesByPaymentMethod'])->name('report.sales.payment_methods')->middleware(['auth', 'can:report.payments.index']);

Route::controller(BuyController::class)->prefix('buys')->middleware(['auth', 'can:admin.buys'])->group(function() {
    Route::get('/'                          , 'index')->name('admin.buys');
    Route::get('/create'                    , 'create')->name('admin.create_buy');
    Route::get('/get'                       , 'get')->name('buys.get');
    Route::post('/save'                     , 'save')->name('admin.save_buy');
    Route::post('/detail'                   , 'detail')->name('admin.detail_buy');
    Route::post('/store'                    , 'store')->name('admin.store_buy');
    Route::post('/delete'                   , 'delete')->name('admin.delete_buy');
    Route::post('/load-cart'                , 'load_cart')->name('admin.load_cart_buys');
    Route::post('/get-price'                , 'get_product')->name('admin.get_product_buy_purchase');
    Route::post('/add-product'              , 'add_product')->name('admin.add_product_buy');
    Route::post('/delete-product'           , 'delete_product')->name('admin.delete_product_buy');
    Route::post('/store-product'            , 'store_product')->name('admin.store_product_buy');
    Route::post('/load-providers'           , 'load_providers')->name('admin.load_providers');
    Route::post('/get-product-warehouse'    , 'get_product_idwarehouse')->name('admin.get_products_by_idwarehouse_b');

    Route::post('/print-buy'                , 'print')->name('admin.print_buy');
    Route::get('/pdf'                       , 'test_pdf');
});
