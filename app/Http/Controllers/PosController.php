<?php

namespace App\Http\Controllers;

use App\Models\ArchingCash;
use App\Models\Billing;
use App\Models\Business;
use App\Models\Client;
use App\Models\DetailBilling;
use App\Models\DetailPayment;
use App\Models\DetailSaleNote;
use App\Models\IdentityDocumentType;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\SaleNote;
use App\Models\Serie;
use App\Models\StockProduct;
use App\Models\TypeDocument;
use App\Models\Warehouse;
use App\Services\Ebilling\Payload\BillingPayloadBuilder;
use App\Services\Ebilling\SunatDispatchService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PosController extends Controller
{
    public function index()
    {
        $billingSummary = $this->billingsByCurrentWarehouse()
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->whereIn('type_documents.codigo', ['01', '03']);

        $saleNoteSummary = $this->saleNotesByCurrentWarehouse();

        return view('admin.pos.list', [
            'kpi_boletas_count' => (clone $billingSummary)->where('type_documents.codigo', '03')->whereDate('billings.fecha_emision', Carbon::today())->count(),
            'kpi_boletas_total' => (clone $billingSummary)->where('type_documents.codigo', '03')->whereDate('billings.fecha_emision', Carbon::today())->sum('billings.total'),
            'kpi_facturas_count' => (clone $billingSummary)->where('type_documents.codigo', '01')->whereDate('billings.fecha_emision', Carbon::today())->count(),
            'kpi_facturas_total' => (clone $billingSummary)->where('type_documents.codigo', '01')->whereDate('billings.fecha_emision', Carbon::today())->sum('billings.total'),
            'kpi_sale_notes_count' => (clone $saleNoteSummary)->whereDate('fecha_emision', Carbon::today())->count(),
            'kpi_sale_notes_total' => (clone $saleNoteSummary)->whereDate('fecha_emision', Carbon::today())->sum('total'),
            'signo' => $this->signo_pais(),
        ]);
    }

    public function create()
    {
        $data['signo'] = $this->signo_pais();
        $data['typeDocuments'] = IdentityDocumentType::query()
            ->where('estado', 1)
            ->orderBy('descripcion')
            ->get(['id', 'codigo', 'descripcion']);

        $idusuario = (int) Auth::user()['id'];
        $idcaja = (int) Auth::user()['idcaja'];
        $siExisteArqueo = ArchingCash::where('idcaja', $idcaja)
            ->where('idusuario', $idusuario)
            ->latest('id')
            ->first();

        if (! $siExisteArqueo || (int) $siExisteArqueo->estado !== 1) {
            return redirect()
                ->route('admin.arching_cashes')
                ->with('message', 'Primero debe aperturar su caja para comenzar a vender.')
                ->with('message_type', 'warning');
        }

        $this->destroy_cart();

        return view('admin.pos.home', $data);
    }

    public function get()
    {
        $billingDocuments = $this->billingsByCurrentWarehouse()
            ->selectRaw("
                billings.id as record_id,
                'billing' as source,
                billings.fecha_emision as issue_date,
                CONCAT(billings.serie, '-', billings.correlativo) as document_number,
                clients.nro_documento as customer_document,
                clients.nombres as customer_name,
                type_documents.descripcion as document_type,
                type_documents.codigo as document_code,
                billings.total as total,
                CASE
                    WHEN billings.anulado = 1 THEN 'Anulado'
                    ELSE 'Vigente'
                END as status_label
            ")
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->whereIn('type_documents.codigo', ['01', '03']);

        $saleNoteDocuments = $this->saleNotesByCurrentWarehouse()
            ->selectRaw("
                sale_notes.id as record_id,
                'sale_note' as source,
                sale_notes.fecha_emision as issue_date,
                CONCAT(sale_notes.serie, '-', sale_notes.correlativo) as document_number,
                clients.nro_documento as customer_document,
                clients.nombres as customer_name,
                'Nota de venta' as document_type,
                '02' as document_code,
                sale_notes.total as total,
                CASE
                    WHEN sale_notes.estado = '2' THEN 'Anulado'
                    WHEN sale_notes.estado = '1' THEN 'Pagado'
                    ELSE 'Registrado'
                END as status_label
            ")
            ->join('clients', 'sale_notes.idcliente', '=', 'clients.id');

        $documents = DB::query()
            ->fromSub($billingDocuments->unionAll($saleNoteDocuments), 'documents');

        if (request()->has('columns')) {
            $dateSearch = trim((string) request()->input('columns.0.search.value'));
            $typeSearch = trim((string) request()->input('columns.1.search.value'));
            $documentSearch = trim((string) request()->input('columns.2.search.value'));
            $customerSearch = trim((string) request()->input('columns.3.search.value'));
            $totalSearch = trim((string) request()->input('columns.4.search.value'));
            $statusSearch = trim((string) request()->input('columns.5.search.value'));

            if ($dateSearch !== '') {
                $documents->whereDate('issue_date', $dateSearch);
            }

            if ($typeSearch !== '') {
                $documents->where('document_type', 'like', '%' . $typeSearch . '%');
            }

            if ($documentSearch !== '') {
                $documents->where('document_number', 'like', '%' . $documentSearch . '%');
            }

            if ($customerSearch !== '') {
                $documents->where(function ($query) use ($customerSearch) {
                    $query->where('customer_name', 'like', '%' . $customerSearch . '%')
                        ->orWhere('customer_document', 'like', '%' . $customerSearch . '%');
                });
            }

            if ($totalSearch !== '') {
                $documents->where('total', 'like', '%' . str_replace(',', '.', $totalSearch) . '%');
            }

            if ($statusSearch !== '') {
                $documents->where('status_label', 'like', '%' . $statusSearch . '%');
            }
        }

        $documents->orderByDesc('issue_date')->orderByDesc('record_id');

        return datatables()
            ->of($documents)
            ->editColumn('issue_date', fn ($document) => Carbon::parse((string) $document->issue_date)->format('Y-m-d'))
            ->addColumn('document_type_badge', function ($document) {
                $class = match ((string) $document->document_code) {
                    '01' => 'bg-info-subtle text-info',
                    '03' => 'bg-primary-subtle text-primary',
                    default => 'bg-success-subtle text-success',
                };

                return '<span class="badge ' . $class . '">' . e((string) $document->document_type) . '</span>';
            })
            ->addColumn('document_info', function ($document) {
                return '<div class="text-center"><div class="fw-semibold">' . e((string) $document->document_number) . '</div></div>';
            })
            ->addColumn('customer_info', function ($document) {
                return '<div class="billing-customer-cell">'
                    . '<div class="billing-customer-name">' . e((string) $document->customer_name) . '</div>'
                    . '<small class="billing-customer-doc">' . e((string) ($document->customer_document ?: 'Sin documento')) . '</small>'
                    . '</div>';
            })
            ->addColumn('total_badge', fn ($document) => '<div class="billing-total-chip">' . e($this->signo_pais() . ' ' . number_format((float) $document->total, 2, '.', '')) . '</div>')
            ->addColumn('status_badge', function ($document) {
                $class = match ((string) $document->status_label) {
                    'Anulado' => 'bg-danger-subtle text-danger',
                    'Pagado' => 'bg-success-subtle text-success',
                    'Vigente' => 'bg-success-subtle text-success',
                    default => 'bg-light text-dark border',
                };

                return '<span class="badge ' . $class . '">' . e((string) $document->status_label) . '</span>';
            })
            ->rawColumns(['document_type_badge', 'document_info', 'customer_info', 'total_badge', 'status_badge'])
            ->toJson();
    }

    public function load_cart(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $cart = $this->create_cart();
        $html_cart = '';
        $html_totales = '';
        $contador = 0;
        $signo = $this->signo_pais();

        if (! empty($cart['products'])) {
            foreach ($cart['products'] as $product) {
                $contador++;
                $subtotal = number_format(((float) $product['precio_venta'] * (float) $product['cantidad']), 2, '.', '');
                $precioVenta = number_format((float) $product['precio_venta'], 2, '.', '');

                $html_cart .= '<tr id="row-' . $contador . '">
                    <td class="align-middle">' . e((string) $product['descripcion']) . '</td>
                    <td class="text-center align-middle">
                        <input type="text" class="form-control form-control-sm text-center input-update"
                            value="' . $precioVenta . '"
                            data-cantidad="' . e((string) $product['cantidad']) . '"
                            data-id="' . e((string) $product['id']) . '"
                            name="precio_venta">
                    </td>
                    <td class="text-center align-middle">
                        <div class="input-group input-group-sm">
                            <button class="btn btn-light border btn-down" type="button"
                                data-id="' . e((string) $product['id']) . '"
                                data-cantidad="' . e((string) $product['cantidad']) . '"
                                data-precio_venta="' . e((string) $product['precio_venta']) . '">
                                <i class="ri-subtract-line"></i>
                            </button>
                            <input type="text" class="form-control text-center input-quantity"
                                value="' . e((string) $product['cantidad']) . '"
                                data-id="' . e((string) $product['id']) . '"
                                data-precio_venta="' . e((string) $product['precio_venta']) . '"
                                min="0" style="max-width: 60px;">
                            <button class="btn btn-light border btn-up" type="button"
                                data-id="' . e((string) $product['id']) . '"
                                data-cantidad="' . e((string) $product['cantidad']) . '"
                                data-precio_venta="' . e((string) $product['precio_venta']) . '">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </td>
                    <td class="text-center align-middle fw-bold">' . $subtotal . '</td>
                    <td class="text-center align-middle">
                        <button class="btn btn-sm btn-danger btn-delete-product"
                            data-id="' . e((string) $product['id']) . '"
                            title="Eliminar">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>';
            }
        } else {
            $html_cart .= '<tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <div class="d-flex flex-column align-items-center">
                        <div class="fw-semibold mb-1">Tu carrito está vacío</div>
                        <small>Busca o escanea un producto para comenzar la venta.</small>
                    </div>
                </td>
            </tr>';
        }

        $html_totales .= '<p>Subtotal: <span id="subtotal" class="float-end">' . $signo . number_format((float) $cart['subtotal'], 2, '.', '') . '</span></p>
                        <p>IGV: <span id="igv" class="float-end">' . $signo . number_format((float) $cart['igv'], 2, '.', '') . '</span></p>
                        <hr>
                        <h5>Total: <span id="total" class="float-end">' . $signo . number_format((float) $cart['total'], 2, '.', '') . '</span></h5>';

        return response()->json([
            'status' => true,
            'cart_products' => $cart,
            'cantidad_prod' => count($cart['products']),
            'html_cart' => $html_cart,
            'html_totales' => $html_totales,
            'total_cart' => number_format((float) $cart['total'], 2, '.', ''),
        ]);
    }

    public function search_product(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $value = trim((string) $request->input('search'));
        $idalmacen = (int) $request->input('idalmacen');

        $productos = Product::select(
            'products.*',
            'units.codigo as unidad',
            'stock_products.stock_actual as stock',
            'stock_products.idalmacen as idalmacen',
            'stock_products.precio_venta'
        )
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->join('categories', 'products.idcategoria', '=', 'categories.id')
            ->join('stock_products', 'products.id', '=', 'stock_products.idproducto')
            ->join('warehouses', 'stock_products.idalmacen', '=', 'warehouses.id')
            ->where('warehouses.id', $idalmacen)
            ->where(function ($query) use ($value) {
                $query->where('products.descripcion', 'like', '%' . $value . '%')
                    ->orWhere('categories.descripcion', 'like', '%' . $value . '%')
                    ->orWhere('products.codigo_barras', 'like', '%' . $value . '%')
                    ->orWhere('products.codigo_interno', 'like', '%' . $value . '%');
            })
            ->limit(6)
            ->get();

        $datos = $productos->map(function ($producto) {
            $signo = $this->signo_pais();
            $precio = $signo . number_format((float) $producto->precio_venta, 2);

            return [
                'label' => $producto->descripcion,
                'nombre' => $producto->descripcion,
                'codigo' => $producto->codigo_barras,
                'marca' => $producto->marca,
                'precio' => $precio,
                'stock' => $producto->stock,
                'idproducto' => $producto->id,
                'idalmacen' => $producto->idalmacen,
                'texto_limpio' => $producto->descripcion,
                'opcion' => $producto->opcion,
            ];
        });

        return response()->json($datos);
    }

    public function add_product_search(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idproducto = (int) $request->input('idproducto');
        $producto = Product::where('id', $idproducto)->first();

        if (! $producto) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no se encuentra en el almacen.',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $stockProducto = StockProduct::where('idalmacen', $idalmacen)
            ->where('idproducto', $producto->id)
            ->first();

        if (! $stockProducto) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no se encuentra en el almacen seleccionado.',
                'type' => 'warning',
            ]);
        }

        $agregar = $this->add_product_cart(
            $producto->id,
            1,
            number_format((float) $stockProducto->precio_venta, 2, '.', ''),
            (int) $producto->opcion,
            $idalmacen
        );

        if (! $agregar['status']) {
            return response()->json([
                'status' => false,
                'msg' => $agregar['msg'],
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Producto agregado correctamente',
            'type' => 'success',
        ]);
    }

    public function delete_product(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = (int) $request->input('id');
        $producto = Product::where('id', $id)->first();

        if (! $producto) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no existe.',
                'type' => 'warning',
            ]);
        }

        if (! $this->delete_product_cart($id, (int) $producto->opcion)) {
            return response()->json([
                'status' => false,
                'msg' => 'No se pudo eliminar el producto',
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
            'type' => 'success',
        ]);
    }

    public function clear_cart(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        if (! session()->has('pos')) {
            return response()->json([
                'status' => true,
                'msg' => 'El carrito ya esta vacio.',
                'type' => 'info',
            ]);
        }

        $this->destroy_cart();

        return response()->json([
            'status' => true,
            'msg' => 'Carrito vaciado correctamente.',
            'type' => 'success',
        ]);
    }

    public function store_product(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = (int) $request->input('id');
        $producto = Product::where('id', $id)->first();

        if (! $producto) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no existe.',
                'type' => 'warning',
            ]);
        }

        $cantidad = (int) $request->input('cantidad');
        $precio = number_format((float) $request->input('precio'), 2, '.', '');

        if (! $this->update_quantity($id, $cantidad, $precio, (int) $producto->opcion)) {
            return response()->json([
                'status' => false,
                'msg' => 'Stock insuficiente',
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Actualizado correctamente',
            'type' => 'success',
        ]);
    }

    public function add_product_barcode(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $barcode = trim((string) $request->input('barcode'));
        $producto = Product::where('codigo_barras', $barcode)->first();

        if (! $producto) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no se encuentra en el inventario',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $stockProducto = StockProduct::where('idalmacen', $idalmacen)
            ->where('idproducto', $producto->id)
            ->first();

        if (! $stockProducto) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no se encuentra en el almacen',
                'type' => 'warning',
            ]);
        }

        $agregar = $this->add_product_cart(
            $producto->id,
            1,
            number_format((float) $stockProducto->precio_venta, 2, '.', ''),
            (int) $producto->opcion,
            $idalmacen
        );

        if (! $agregar['status']) {
            return response()->json([
                'status' => false,
                'msg' => $agregar['msg'],
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Producto agregado correctamente',
            'type' => 'success',
        ]);
    }

    public function open_modal(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $signo = $this->signo_pais();
        $cart = $this->create_cart();
        $subtotal = number_format((float) $cart['subtotal'], 2, '.', '');
        $igv = number_format((float) $cart['igv'], 2, '.', '');
        $total = number_format((float) $cart['total'], 2, '.', '');
        $payModes = PayMode::orderBy('descripcion')->get();
        $documentTypes = $this->getPosDocumentTypes();
        $defaultDocumentType = $documentTypes->firstWhere('codigo', '03') ?? $documentTypes->first();

        return response()->json([
            'status' => true,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'pay_modes' => $payModes,
            'document_types' => $documentTypes->values(),
            'default_document_type_id' => $defaultDocumentType?->id,
            'signo' => $signo,
        ]);
    }

    public function save_sale(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        return $this->saveSaleModern($request);

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer|exists:clients,id',
            'document_type_id' => 'required|integer|exists:type_documents,id',
            'payment_condition' => 'required|string|in:contado,credito',
            'global_discount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.method_id' => 'required_with:payments|integer|exists:pay_modes,id',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'installments' => 'nullable|array',
            'installments.*.amount' => 'required_with:installments|numeric|min:0.01',
            'installments.*.due_date' => 'required_with:installments|date',
        ], [
            'client_id.required' => 'Debe seleccionar un cliente.',
            'document_type_id.required' => 'Debe seleccionar el tipo de comprobante.',
            'payments.required' => 'Debe agregar al menos un método de pago.',
            'payments.min' => 'Debe agregar al menos un método de pago.',
            'payments.*.method_id.required' => 'Debe seleccionar un método de pago válido.',
            'payments.*.amount.required' => 'Debe ingresar un monto válido en los pagos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $cart = $this->create_cart();

        if (empty($cart['products'])) {
            return response()->json([
                'status' => false,
                'msg' => 'Ingrese al menos 1 producto',
                'type' => 'warning',
            ], 422);
        }

        foreach ($cart['products'] as $product) {
            if ((float) $product['precio_venta'] <= 0) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Ingrese un precio válido para ' . $product['descripcion'],
                    'type' => 'warning',
                ], 422);
            }
        }

        $documentType = $this->getPosDocumentTypes()->firstWhere('id', (int) $data['document_type_id']);
        if (! $documentType) {
            return response()->json([
                'status' => false,
                'msg' => 'El tipo de comprobante no está permitido en POS.',
                'type' => 'warning',
            ], 422);
        }

        $client = Client::with('tipoDocumento')->find((int) $data['client_id']);
        $clientDocumentCode = trim((string) optional($client?->tipoDocumento)->codigo);
        if ((string) $documentType->codigo === '01' && $clientDocumentCode !== '6') {
            return response()->json([
                'status' => false,
                'msg' => 'Para emitir factura, el cliente debe tener RUC.',
                'type' => 'warning',
            ], 422);
        }

        $totalPaid = round((float) collect($data['payments'])->sum(function ($payment) {
            return (float) $payment['amount'];
        }), 2);
        $cartTotal = round((float) $cart['total'], 2);

        if ($totalPaid + 0.009 < $cartTotal) {
            return response()->json([
                'status' => false,
                'msg' => 'El total pagado no puede ser menor al total de la venta.',
                'type' => 'warning',
            ], 422);
        }

        $idusuario = (int) Auth::user()['id'];
        $idcaja = (int) Auth::user()['idcaja'];
        $idalmacen = (int) Auth::user()['idalmacen'];
        $fechaEmision = date('Y-m-d');
        $fechaVencimiento = $fechaEmision;
        $hora = date('H:i:s');
        $arqueo = ArchingCash::where('idcaja', $idcaja)
            ->where('idusuario', $idusuario)
            ->where('estado', 1)
            ->latest('id')
            ->first();

        if (! $arqueo) {
            return response()->json([
                'status' => false,
                'msg' => 'No se encontró una caja abierta para registrar la venta.',
                'type' => 'warning',
            ], 422);
        }

        $serieModel = Serie::where('idtipo_documento', (int) $documentType->id)
            ->where('idcaja', $idcaja)
            ->where('estado', 1)
            ->orderBy('id')
            ->first();

        if (! $serieModel) {
            return response()->json([
                'status' => false,
                'msg' => 'No existe una serie configurada para ese comprobante en la caja actual.',
                'type' => 'warning',
            ], 422);
        }

        $paymentBreakdown = $this->buildPaymentBreakdown($data['payments']);
        $firstPayMethodId = (int) ($paymentBreakdown[0]['id'] ?? $data['payments'][0]['method_id']);
        $change = max(0, round($totalPaid - $cartTotal, 2));
        $baseName = $documentType->codigo . '-' . $serieModel->serie . '-' . $serieModel->correlativo;

        try {
            $result = DB::transaction(function () use (
                $documentType,
                $client,
                $cart,
                $fechaEmision,
                $fechaVencimiento,
                $hora,
                $idusuario,
                $arqueo,
                $serieModel,
                $paymentBreakdown,
                $firstPayMethodId,
                $change,
                $idalmacen,
                $baseName
            ) {
                $this->validateStockBeforeSale($cart);

                if ((string) $documentType->codigo === '02') {
                    $document = SaleNote::create([
                        'idtipo_comprobante' => (int) $documentType->id,
                        'serie' => $serieModel->serie,
                        'correlativo' => $serieModel->correlativo,
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'hora' => $hora,
                        'idcliente' => (int) $client->id,
                        'subtotal' => $cart['subtotal'],
                        'igv' => $cart['igv'],
                        'total' => $cart['total'],
                        'observaciones' => '',
                        'estado' => 1,
                        'idusuario' => $idusuario,
                        'idarqueocaja' => $arqueo->id,
                        'vuelto' => $change,
                    ]);

                    foreach ($cart['products'] as $product) {
                        DetailSaleNote::create([
                            'idnotaventa' => $document->id,
                            'idproducto' => $product['id'],
                            'cantidad' => $product['cantidad'],
                            'igv' => $product['igv'],
                            'precio_unitario' => $product['precio_venta'],
                            'precio_total' => ((float) $product['precio_venta'] * (float) $product['cantidad']),
                            'opcion' => $product['opcion'],
                            'idalmacen' => $product['idalmacen'],
                        ]);
                    }

                    $documentId = $document->id;
                    $documentKind = 'sale_note';
                } else {
                    $document = Billing::create([
                        'idtipo_comprobante' => (int) $documentType->id,
                        'serie' => $serieModel->serie,
                        'correlativo' => $serieModel->correlativo,
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'hora' => $hora,
                        'idcliente' => (int) $client->id,
                        'idmoneda' => 1,
                        'idpago' => $firstPayMethodId,
                        'modo_pago' => $firstPayMethodId,
                        'sunat_forma_pago' => 'Contado',
                        'exonerada' => 0,
                        'inafecta' => 0,
                        'gravada' => $cart['subtotal'],
                        'anticipo' => 0,
                        'igv' => $cart['igv'],
                        'icbper' => 0,
                        'gratuita' => 0,
                        'otros_cargos' => 0,
                        'total' => $cart['total'],
                        'monto_credito' => 0,
                        'cuotas' => null,
                        'payment_breakdown' => $paymentBreakdown,
                        'observaciones' => '',
                        'cdr' => null,
                        'anulado' => false,
                        'id_tipo_nota_credito' => null,
                        'idfactura_anular' => null,
                        'motivo' => null,
                        'estado_cpe' => null,
                        'errores' => null,
                        'nticket' => $baseName,
                        'idusuario' => $idusuario,
                        'idarqueocaja' => $arqueo->id,
                        'vuelto' => $change,
                        'qr' => null,
                        'idalmacen' => $idalmacen,
                    ]);

                    foreach ($cart['products'] as $product) {
                        $igvFactor = $this->resolveIgvFactor((int) $product['igv']);
                        $valorUnitario = $igvFactor > 0 ? round(((float) $product['precio_venta'] / $igvFactor), 10) : (float) $product['precio_venta'];
                        $valorTotal = round($valorUnitario * (float) $product['cantidad'], 2);
                        $precioTotal = round((float) $product['precio_venta'] * (float) $product['cantidad'], 2);
                        $igvAmount = round($precioTotal - $valorTotal, 2);

                        DetailBilling::create([
                            'idfacturacion' => $document->id,
                            'idproducto' => $product['id'],
                            'cantidad' => $product['cantidad'],
                            'descuento' => 0,
                            'igv' => $igvAmount,
                            'icbper' => 0,
                            'factor_icbper' => null,
                            'cantidad_bolsas' => 0,
                            'id_afectacion_igv' => (int) ($product['idcodigo_igv'] ?? 1),
                            'precio_unitario' => $product['precio_venta'],
                            'valor_unitario' => $valorUnitario,
                            'valor_total' => $valorTotal,
                            'precio_total' => $precioTotal,
                        ]);
                    }

                    $documentId = $document->id;
                    $documentKind = 'billing';
                }

                foreach ($cart['products'] as $product) {
                    if ((int) $product['opcion'] !== 1) {
                        continue;
                    }

                    $registro = StockProduct::where('idproducto', $product['id'])
                        ->where('idalmacen', $product['idalmacen'])
                        ->lockForUpdate()
                        ->first();

                    $nuevoStock = max(0, (int) $registro->stock_actual - (int) $product['cantidad']);
                    $registro->update([
                        'stock_actual' => $nuevoStock,
                    ]);
                }

                foreach ($paymentBreakdown as $payment) {
                    DetailPayment::create([
                        'idtipo_comprobante' => (int) $documentType->id,
                        'idfactura' => $documentId,
                        'idpago' => (int) $payment['id'],
                        'monto' => number_format((float) $payment['monto'], 2, '.', ''),
                        'idarqueocaja' => $arqueo->id,
                        'estado' => 1,
                    ]);
                }

                $this->advanceSerieCorrelative($serieModel);

                if ($documentKind === 'billing') {
                    $this->attemptSunatDispatch($document);
                }

                if ($documentKind === 'sale_note') {
                    $ticketUrl = $this->generateSaleNoteTicket($documentId, $baseName);
                } else {
                    $ticketUrl = $this->generateBillingTicket($documentId, $baseName);
                }

                return [
                    'document_id' => $documentId,
                    'document_kind' => $documentKind,
                    'ticket_url' => $ticketUrl,
                    'base_name' => $baseName,
                ];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage() ?: 'No se pudo registrar la venta.',
                'type' => 'warning',
            ], 422);
        }

        $this->destroy_cart();

        return response()->json([
            'status' => true,
            'id' => $result['document_id'],
            'document_kind' => $result['document_kind'],
            'ticket_url' => $result['ticket_url'],
            'pdf' => $result['base_name'] . '.pdf',
            'type_document' => (int) $documentType->id,
        ]);
    }

    protected function saveSaleModern(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer|exists:clients,id',
            'document_type_id' => 'required|integer|exists:type_documents,id',
            'payment_condition' => 'required|string|in:contado,credito',
            'global_discount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.method_id' => 'required_with:payments|integer|exists:pay_modes,id',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'installments' => 'nullable|array',
            'installments.*.amount' => 'required_with:installments|numeric|min:0.01',
            'installments.*.due_date' => 'required_with:installments|date',
        ], [
            'client_id.required' => 'Debe seleccionar un cliente.',
            'document_type_id.required' => 'Debe seleccionar el tipo de comprobante.',
            'payment_condition.required' => 'Debe seleccionar la condicion de pago.',
            'payment_condition.in' => 'La condicion de pago no es valida.',
            'payments.*.method_id.required_with' => 'Debe seleccionar un metodo de pago valido.',
            'payments.*.amount.required_with' => 'Debe ingresar un monto valido en los pagos.',
            'installments.*.amount.required_with' => 'Debe ingresar un monto valido para cada cuota.',
            'installments.*.due_date.required_with' => 'Debe indicar el vencimiento de cada cuota.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $cart = $this->create_cart();

        if (empty($cart['products'])) {
            return response()->json([
                'status' => false,
                'msg' => 'Ingrese al menos un producto.',
                'type' => 'warning',
            ], 422);
        }

        foreach ($cart['products'] as $product) {
            if ((float) $product['precio_venta'] <= 0) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Ingrese un precio valido para ' . $product['descripcion'] . '.',
                    'type' => 'warning',
                ], 422);
            }
        }

        $documentType = $this->getPosDocumentTypes()->firstWhere('id', (int) $data['document_type_id']);
        if (! $documentType) {
            return response()->json([
                'status' => false,
                'msg' => 'El tipo de comprobante no esta permitido en POS.',
                'type' => 'warning',
            ], 422);
        }

        $client = Client::with('tipoDocumento')->find((int) $data['client_id']);
        $clientValidation = $this->validateClientForPosDocument($client, $documentType);
        if (! $clientValidation['status']) {
            return response()->json([
                'status' => false,
                'msg' => $clientValidation['msg'],
                'type' => 'warning',
            ], 422);
        }

        $paymentCondition = (string) $data['payment_condition'];
        $saleBreakdown = $this->buildDiscountedSaleBreakdown($cart, round((float) ($data['global_discount'] ?? 0), 2));
        $finalTotal = round((float) $saleBreakdown['total'], 2);

        if ($finalTotal <= 0) {
            return response()->json([
                'status' => false,
                'msg' => 'El total final de la venta debe ser mayor a cero.',
                'type' => 'warning',
            ], 422);
        }

        $paymentBreakdown = [];
        $installments = [];
        $change = 0.00;
        $creditAmount = 0.00;
        $firstPayMethodId = 1;

        if ($paymentCondition === 'contado') {
            $payments = collect($data['payments'] ?? [])->filter(function ($payment) {
                return isset($payment['method_id'], $payment['amount']) && (float) $payment['amount'] > 0;
            })->values()->all();

            if (count($payments) < 1) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Debe agregar al menos un metodo de pago valido.',
                    'type' => 'warning',
                ], 422);
            }

            $paymentBreakdown = $this->buildPaymentBreakdown($payments);
            $totalPaid = round((float) collect($paymentBreakdown)->sum('monto'), 2);

            if ($totalPaid + 0.009 < $finalTotal) {
                return response()->json([
                    'status' => false,
                    'msg' => 'El total pagado no puede ser menor al total de la venta.',
                    'type' => 'warning',
                ], 422);
            }

            $firstPayMethodId = (int) ($paymentBreakdown[0]['id'] ?? $payments[0]['method_id']);
            $change = max(0, round($totalPaid - $finalTotal, 2));
        } else {
            $installments = $this->buildInstallmentsBreakdown($data['installments'] ?? []);

            if (count($installments) < 1) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Debe registrar al menos una cuota para la venta al credito.',
                    'type' => 'warning',
                ], 422);
            }

            $creditAmount = round((float) collect($installments)->sum('monto'), 2);
            if (abs($creditAmount - $finalTotal) > 0.01) {
                return response()->json([
                    'status' => false,
                    'msg' => 'La suma de cuotas debe coincidir con el total final de la venta.',
                    'type' => 'warning',
                ], 422);
            }

            $paymentBreakdown = collect($installments)->map(function ($installment, $index) {
                return [
                    'id' => 0,
                    'descripcion' => 'Cuota ' . ($index + 1),
                    'monto' => number_format((float) $installment['monto'], 2, '.', ''),
                ];
            })->values()->all();
        }

        $idusuario = (int) Auth::user()['id'];
        $idcaja = (int) Auth::user()['idcaja'];
        $idalmacen = (int) Auth::user()['idalmacen'];
        $fechaEmision = date('Y-m-d');
        $fechaVencimiento = $paymentCondition === 'credito'
            ? (collect($installments)->pluck('fecha_vencimiento')->filter()->sort()->first() ?: $fechaEmision)
            : $fechaEmision;
        $hora = date('H:i:s');
        $arqueo = ArchingCash::where('idcaja', $idcaja)
            ->where('idusuario', $idusuario)
            ->where('estado', 1)
            ->latest('id')
            ->first();

        if (! $arqueo) {
            return response()->json([
                'status' => false,
                'msg' => 'No se encontro una caja abierta para registrar la venta.',
                'type' => 'warning',
            ], 422);
        }

        $serieModel = Serie::where('idtipo_documento', (int) $documentType->id)
            ->where('idcaja', $idcaja)
            ->where('estado', 1)
            ->orderBy('id')
            ->first();

        if (! $serieModel) {
            return response()->json([
                'status' => false,
                'msg' => 'No existe una serie configurada para ese comprobante en la caja actual.',
                'type' => 'warning',
            ], 422);
        }

        $baseName = $documentType->codigo . '-' . $serieModel->serie . '-' . $serieModel->correlativo;

        try {
            $result = DB::transaction(function () use (
                $documentType,
                $client,
                $cart,
                $fechaEmision,
                $fechaVencimiento,
                $hora,
                $idusuario,
                $arqueo,
                $serieModel,
                $saleBreakdown,
                $paymentCondition,
                $paymentBreakdown,
                $installments,
                $firstPayMethodId,
                $change,
                $creditAmount,
                $idalmacen,
                $baseName
            ) {
                $this->validateStockBeforeSale($cart);

                if ((string) $documentType->codigo === '02') {
                    $document = SaleNote::create([
                        'idtipo_comprobante' => (int) $documentType->id,
                        'serie' => $serieModel->serie,
                        'correlativo' => $serieModel->correlativo,
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'hora' => $hora,
                        'idcliente' => (int) $client->id,
                        'modo_pago' => $paymentCondition === 'credito' ? 2 : 1,
                        'subtotal' => $saleBreakdown['subtotal'],
                        'igv' => $saleBreakdown['igv'],
                        'total' => $saleBreakdown['total'],
                        'monto_credito' => $paymentCondition === 'credito' ? $creditAmount : 0,
                        'cuotas' => $paymentCondition === 'credito' ? $installments : null,
                        'payment_breakdown' => $paymentBreakdown,
                        'observaciones' => '',
                        'estado' => $paymentCondition === 'credito' ? 0 : 1,
                        'idusuario' => $idusuario,
                        'idarqueocaja' => $arqueo->id,
                        'vuelto' => $change,
                    ]);

                    foreach ($saleBreakdown['items'] as $product) {
                        DetailSaleNote::create([
                            'idnotaventa' => $document->id,
                            'idproducto' => $product['id'],
                            'cantidad' => $product['cantidad'],
                            'igv' => $product['igv'],
                            'precio_unitario' => $product['precio_unitario_descuento'],
                            'precio_total' => $product['precio_total_descuento'],
                            'descuento' => $product['descuento'],
                            'opcion' => $product['opcion'],
                            'idalmacen' => $product['idalmacen'],
                        ]);
                    }

                    $documentId = $document->id;
                    $documentKind = 'sale_note';
                } else {
                    $document = Billing::create([
                        'idtipo_comprobante' => (int) $documentType->id,
                        'serie' => $serieModel->serie,
                        'correlativo' => $serieModel->correlativo,
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'hora' => $hora,
                        'idcliente' => (int) $client->id,
                        'idmoneda' => 1,
                        'idpago' => $firstPayMethodId,
                        'modo_pago' => $paymentCondition === 'credito' ? 2 : 1,
                        'sunat_forma_pago' => $paymentCondition === 'credito' ? 'Credito' : 'Contado',
                        'exonerada' => 0,
                        'inafecta' => 0,
                        'gravada' => $saleBreakdown['subtotal'],
                        'anticipo' => 0,
                        'igv' => $saleBreakdown['igv'],
                        'icbper' => 0,
                        'gratuita' => 0,
                        'otros_cargos' => 0,
                        'total' => $saleBreakdown['total'],
                        'monto_credito' => $paymentCondition === 'credito' ? $creditAmount : 0,
                        'cuotas' => $paymentCondition === 'credito' ? $installments : null,
                        'payment_breakdown' => $paymentBreakdown,
                        'observaciones' => '',
                        'cdr' => null,
                        'anulado' => false,
                        'id_tipo_nota_credito' => null,
                        'idfactura_anular' => null,
                        'motivo' => null,
                        'estado_cpe' => null,
                        'errores' => null,
                        'nticket' => $baseName,
                        'idusuario' => $idusuario,
                        'idarqueocaja' => $arqueo->id,
                        'vuelto' => $change,
                        'qr' => null,
                        'idalmacen' => $idalmacen,
                    ]);

                    foreach ($saleBreakdown['items'] as $product) {
                        DetailBilling::create([
                            'idfacturacion' => $document->id,
                            'idproducto' => $product['id'],
                            'cantidad' => $product['cantidad'],
                            'descuento' => $product['descuento'],
                            'igv' => $product['igv_monto'],
                            'icbper' => 0,
                            'factor_icbper' => null,
                            'cantidad_bolsas' => 0,
                            'id_afectacion_igv' => (int) ($product['idcodigo_igv'] ?? 1),
                            'precio_unitario' => $product['precio_unitario_descuento'],
                            'valor_unitario' => $product['valor_unitario_descuento'],
                            'valor_total' => $product['valor_total_descuento'],
                            'precio_total' => $product['precio_total_descuento'],
                        ]);
                    }

                    $documentId = $document->id;
                    $documentKind = 'billing';
                }

                foreach ($cart['products'] as $product) {
                    if ((int) $product['opcion'] !== 1) {
                        continue;
                    }

                    $registro = StockProduct::where('idproducto', $product['id'])
                        ->where('idalmacen', $product['idalmacen'])
                        ->lockForUpdate()
                        ->first();

                    $nuevoStock = max(0, (int) $registro->stock_actual - (int) $product['cantidad']);
                    $registro->update([
                        'stock_actual' => $nuevoStock,
                    ]);
                }

                if ($paymentCondition === 'contado') {
                    foreach ($paymentBreakdown as $payment) {
                        DetailPayment::create([
                            'idtipo_comprobante' => (int) $documentType->id,
                            'idfactura' => $documentId,
                            'idpago' => (int) $payment['id'],
                            'monto' => number_format((float) $payment['monto'], 2, '.', ''),
                            'idarqueocaja' => $arqueo->id,
                            'estado' => 1,
                        ]);
                    }
                }

                $this->advanceSerieCorrelative($serieModel);

                if ($documentKind === 'billing') {
                    $this->attemptSunatDispatch($document);
                    $document->refresh();
                }

                $successMessage = $documentKind === 'sale_note'
                    ? 'La nota de venta ' . $baseName . ' ha sido registrada correctamente.'
                    : $this->resolveBillingSuccessMessage($document);

                return [
                    'document_id' => $documentId,
                    'document_kind' => $documentKind,
                    'ticket_url' => $documentKind === 'sale_note'
                        ? $this->generateSaleNoteTicket($documentId, $baseName)
                        : $this->generateBillingTicket($documentId, $baseName),
                    'base_name' => $baseName,
                    'msg' => $successMessage,
                ];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage() ?: 'No se pudo registrar la venta.',
                'type' => 'warning',
            ], 422);
        }

        $this->destroy_cart();

        return response()->json([
            'status' => true,
            'id' => $result['document_id'],
            'document_kind' => $result['document_kind'],
            'ticket_url' => $result['ticket_url'],
            'pdf' => $result['base_name'] . '.pdf',
            'msg' => $result['msg'] ?? 'Venta registrada correctamente.',
            'type_document' => (int) $documentType->id,
        ]);
    }

    protected function validateClientForPosDocument(?Client $client, TypeDocument $documentType): array
    {
        if (! $client) {
            return ['status' => false, 'msg' => 'Debe seleccionar un cliente valido.'];
        }

        $documentCode = trim((string) $documentType->codigo);
        $clientDocumentCode = trim((string) optional($client->tipoDocumento)->codigo);
        $documentNumber = preg_replace('/\s+/', '', (string) $client->nro_documento);

        if ($documentCode === '02') {
            return ['status' => true];
        }

        if ($documentCode === '01' && ($clientDocumentCode !== '6' || ! preg_match('/^\d{11}$/', $documentNumber))) {
            return ['status' => false, 'msg' => 'Para emitir factura, el cliente debe tener RUC valido.'];
        }

        if ($documentCode === '03' && ($documentNumber === '' || $clientDocumentCode === '')) {
            return ['status' => false, 'msg' => 'Para emitir boleta, el cliente debe tener un documento registrado.'];
        }

        return ['status' => true];
    }

    protected function buildInstallmentsBreakdown(array $installments): array
    {
        return collect($installments)->map(function ($installment, $index) {
            return [
                'nro' => $index + 1,
                'monto' => number_format((float) ($installment['amount'] ?? 0), 2, '.', ''),
                'fecha_vencimiento' => trim((string) ($installment['due_date'] ?? '')),
            ];
        })->filter(function ($installment) {
            return (float) $installment['monto'] > 0 && $installment['fecha_vencimiento'] !== '';
        })->values()->all();
    }

    protected function buildDiscountedSaleBreakdown(array $cart, float $globalDiscount): array
    {
        $items = collect($cart['products'])->map(function ($product) {
            $quantity = max(1, (float) $product['cantidad']);
            $baseTotal = round((float) $product['precio_venta'] * $quantity, 2);

            return array_merge($product, [
                'cantidad' => $quantity,
                'precio_total_base' => $baseTotal,
            ]);
        })->values();

        $grossTotal = round((float) $items->sum('precio_total_base'), 2);
        $discountToApply = round(min(max($globalDiscount, 0), $grossTotal), 2);
        $runningDiscount = $discountToApply;

        $mapped = $items->map(function ($product, $index) use ($items, $grossTotal, $discountToApply, &$runningDiscount) {
            $isLast = $index === ($items->count() - 1);
            $baseTotal = (float) $product['precio_total_base'];
            $lineDiscount = $isLast
                ? round($runningDiscount, 2)
                : round(($grossTotal > 0 ? ($baseTotal / $grossTotal) : 0) * $discountToApply, 2);

            $runningDiscount = round($runningDiscount - $lineDiscount, 2);
            $lineTotal = round(max($baseTotal - $lineDiscount, 0), 2);
            $igvFactor = $this->resolveIgvFactor((int) $product['igv']);
            $valorTotal = $igvFactor > 0 ? round($lineTotal / $igvFactor, 2) : $lineTotal;
            $igvAmount = round($lineTotal - $valorTotal, 2);
            $unitGross = round($lineTotal / max((float) $product['cantidad'], 1), 2);
            $unitNet = $igvFactor > 0 ? round($unitGross / $igvFactor, 10) : $unitGross;

            return array_merge($product, [
                'descuento' => number_format($lineDiscount, 2, '.', ''),
                'precio_total_descuento' => number_format($lineTotal, 2, '.', ''),
                'precio_unitario_descuento' => number_format($unitGross, 2, '.', ''),
                'valor_total_descuento' => number_format($valorTotal, 2, '.', ''),
                'valor_unitario_descuento' => number_format($unitNet, 10, '.', ''),
                'igv_monto' => number_format($igvAmount, 2, '.', ''),
            ]);
        });

        return [
            'discount' => number_format($discountToApply, 2, '.', ''),
            'subtotal' => number_format((float) $mapped->sum('valor_total_descuento'), 2, '.', ''),
            'igv' => number_format((float) $mapped->sum('igv_monto'), 2, '.', ''),
            'total' => number_format((float) $mapped->sum('precio_total_descuento'), 2, '.', ''),
            'items' => $mapped->all(),
        ];
    }

    protected function resolveBillingSuccessMessage(Billing $billing): string
    {
        $label = match ((string) optional($billing->typeDocument)->codigo) {
            '01' => 'La factura',
            '03' => 'La boleta',
            default => 'El comprobante',
        };

        $document = trim($billing->serie . '-' . $billing->correlativo);

        if ((int) ($billing->cdr ?? 0) === 1 && (int) ($billing->estado_cpe ?? -1) === 0) {
            return $label . ' ' . $document . ' ha sido aceptada.';
        }

        if ((int) ($billing->cdr ?? 0) === 1) {
            return $label . ' ' . $document . ' ha sido enviada y procesada.';
        }

        return $label . ' ' . $document . ' ha sido registrada correctamente.';
    }

    protected function getPosDocumentTypes()
    {
        return TypeDocument::query()
            ->where('estado', 1)
            ->whereIn('codigo', ['02', '03', '01'])
            ->orderByRaw("CASE codigo WHEN '03' THEN 1 WHEN '01' THEN 2 WHEN '02' THEN 3 ELSE 4 END")
            ->get(['id', 'codigo', 'descripcion']);
    }

    protected function buildPaymentBreakdown(array $payments): array
    {
        $payModes = PayMode::query()
            ->whereIn('id', collect($payments)->pluck('method_id')->map(fn ($id) => (int) $id)->all())
            ->get(['id', 'descripcion'])
            ->keyBy('id');

        return collect($payments)->map(function ($payment) use ($payModes) {
            $payMode = $payModes->get((int) $payment['method_id']);

            return [
                'id' => (int) $payment['method_id'],
                'descripcion' => (string) ($payMode->descripcion ?? 'Pago'),
                'monto' => number_format((float) $payment['amount'], 2, '.', ''),
            ];
        })->values()->all();
    }

    protected function validateStockBeforeSale(array $cart): void
    {
        foreach ($cart['products'] as $product) {
            if ((int) $product['opcion'] !== 1) {
                continue;
            }

            $registro = StockProduct::where('idproducto', $product['id'])
                ->where('idalmacen', $product['idalmacen'])
                ->lockForUpdate()
                ->first();

            if (! $registro) {
                throw new \RuntimeException('Uno de los productos ya no está disponible en el almacen seleccionado.');
            }

            if ((int) $registro->stock_actual < (int) $product['cantidad']) {
                throw new \RuntimeException('Stock insuficiente para ' . $product['descripcion'] . '.');
            }
        }
    }

    protected function advanceSerieCorrelative(Serie $serieModel): void
    {
        $ultimoCorrelativo = (int) $serieModel->correlativo + 1;
        $serieModel->update([
            'correlativo' => str_pad((string) $ultimoCorrelativo, 8, '0', STR_PAD_LEFT),
        ]);
    }

    protected function resolveIgvFactor(int $igv): float
    {
        return match ($igv) {
            10 => 1.10,
            18 => 1.18,
            default => 1.00,
        };
    }

    protected function generateSaleNoteTicket(int $id, string $name): string
    {
        $saleNote = SaleNote::with(['cliente.tipoDocumento', 'usuario'])->findOrFail($id);
        $typeDocument = TypeDocument::find($saleNote->idtipo_comprobante);
        $business = Business::find(1);
        $payments = DetailPayment::select('detail_payments.*', 'pay_modes.descripcion as modo_pago')
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where('idfactura', $saleNote->id)
            ->where('idtipo_comprobante', $saleNote->idtipo_comprobante)
            ->get();
        $details = DetailSaleNote::select('detail_sale_notes.*', 'products.descripcion as producto')
            ->join('products', 'detail_sale_notes.idproducto', '=', 'products.id')
            ->where('idnotaventa', $saleNote->id)
            ->get();

        $warehouse = Warehouse::find((int) Auth::user()->idalmacen);
        $formatter = new NumeroALetras();
        $data = [
            'name' => $name,
            'business' => $this->resolveBusinessForWarehouse($business, $warehouse),
            'document_label' => $typeDocument?->descripcion ?? 'NOTA DE VENTA',
            'document_number' => $saleNote->serie . ' - ' . $saleNote->correlativo,
            'customer_name' => $saleNote->cliente?->nombres ?? 'Cliente',
            'customer_document_label' => $saleNote->cliente?->tipoDocumento?->descripcion ?? 'Documento',
            'customer_document_value' => $saleNote->cliente?->nro_documento ?? '-',
            'customer_address' => $saleNote->cliente?->direccion ?? '-',
            'issued_at' => date('d/m/Y', strtotime((string) $saleNote->fecha_emision)) . ' ' . $saleNote->hora,
            'seller' => mb_strtoupper((string) ($saleNote->usuario->user ?? '')),
            'items' => $details,
            'subtotal' => $saleNote->subtotal,
            'igv' => $saleNote->igv,
            'total' => $saleNote->total,
            'discount_total' => $details->sum('descuento'),
            'amount_in_words' => $formatter->toWords((float) $saleNote->total, 2),
            'payment_modes' => $payments->count() ? $payments : collect($saleNote->payment_breakdown ?? []),
            'count_payment' => $payments->count() ?: count($saleNote->payment_breakdown ?? []),
            'signo' => $this->signo_pais(),
            'moneda' => $this->moneda_pais(),
            'payment_condition_label' => (int) ($saleNote->modo_pago ?? 1) === 2 ? 'Credito' : 'Contado',
            'installments' => collect($saleNote->cuotas ?? []),
        ];

        return $this->savePosTicket('sale-notes/ticket', $name, $data);
    }

    protected function generateBillingTicket(int $id, string $name): string
    {
        $billing = Billing::with(['customer.tipoDocumento', 'user', 'currency', 'typeDocument', 'warehouse'])->findOrFail($id);
        $business = Business::find(1);
        $payments = DetailPayment::select('detail_payments.*', 'pay_modes.descripcion as modo_pago')
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where('idfactura', $billing->id)
            ->where('idtipo_comprobante', $billing->idtipo_comprobante)
            ->get();
        $details = DetailBilling::select('detail_billings.*', 'products.descripcion as producto')
            ->join('products', 'detail_billings.idproducto', '=', 'products.id')
            ->where('idfacturacion', $billing->id)
            ->get();

        $formatter = new NumeroALetras();
        $qrImage = $this->ensureBillingQrImage($billing);
        $data = [
            'name' => $name,
            'business' => $this->resolveBusinessForWarehouse($business, $billing->warehouse),
            'document_label' => $billing->typeDocument?->descripcion ?? 'COMPROBANTE',
            'document_number' => $billing->serie . ' - ' . $billing->correlativo,
            'customer_name' => $billing->customer?->nombres ?? 'Cliente',
            'customer_document_label' => $billing->customer?->tipoDocumento?->descripcion ?? 'Documento',
            'customer_document_value' => $billing->customer?->nro_documento ?? '-',
            'customer_address' => $billing->customer?->direccion ?? '-',
            'issued_at' => date('d/m/Y', strtotime((string) $billing->fecha_emision)) . ' ' . $billing->hora,
            'seller' => mb_strtoupper((string) ($billing->user->user ?? '')),
            'items' => $details,
            'subtotal' => $billing->gravada,
            'igv' => $billing->igv,
            'total' => $billing->total,
            'discount_total' => $details->sum('descuento'),
            'amount_in_words' => $formatter->toWords((float) $billing->total, 2),
            'payment_modes' => $payments->count() ? $payments : collect($billing->payment_breakdown ?? []),
            'count_payment' => $payments->count() ?: count($billing->payment_breakdown ?? []),
            'signo' => $this->signo_pais(),
            'moneda' => $this->moneda_pais(),
            'qr_image_path' => $qrImage,
            'show_qr' => true,
            'payment_condition_label' => (int) ($billing->modo_pago ?? 1) === 2 ? 'Credito' : 'Contado',
            'installments' => collect($billing->cuotas ?? []),
        ];

        return $this->savePosTicket('billings/ticket', $name, $data);
    }

    protected function savePosTicket(string $folder, string $name, array $data): string
    {
        $customPaper = [0, 0, 226.77, 900.00];
        $path = public_path('files/' . $folder);
        File::ensureDirectoryExists($path);

        $pdf = Pdf::loadView('admin.pos.ticket_document', $data)->setPaper($customPaper, 'portrait');
        $pdf->save($path . DIRECTORY_SEPARATOR . $name . '.pdf');

        return asset('files/' . $folder . '/' . $name . '.pdf');
    }

    protected function ensureBillingQrImage(Billing $billing): ?string
    {
        try {
            $payload = app(BillingPayloadBuilder::class)->build($billing);
        } catch (\Throwable $exception) {
            return null;
        }

        $business = Business::find(1);
        if (! $business) {
            return null;
        }

        $filename = trim((string) ($billing->serie . '-' . $billing->correlativo)) . '.png';
        $relativePath = 'files/billings/qr/' . $filename;
        $absolutePath = public_path($relativePath);

        if (! is_file($absolutePath)) {
            File::ensureDirectoryExists(dirname($absolutePath));

            $qrText = implode('|', [
                (string) ($business->ruc ?? ''),
                (string) ($payload['documento']['tipo'] ?? ''),
                (string) ($payload['documento']['serie'] ?? ''),
                (string) ($payload['documento']['correlativo'] ?? ''),
                number_format((float) ($payload['totales']['igv'] ?? 0), 2, '.', ''),
                number_format((float) ($payload['totales']['importe_total'] ?? 0), 2, '.', ''),
                (string) ($payload['documento']['fecha_emision'] ?? ''),
                (string) ($payload['cliente']['tipo_documento'] ?? ''),
                (string) ($payload['cliente']['numero_documento'] ?? ''),
                '',
            ]);

            File::put(
                $absolutePath,
                QrCode::format('png')->size(140)->margin(0)->generate($qrText)
            );
        }

        if ($billing->qr !== $relativePath) {
            $billing->forceFill(['qr' => $relativePath])->saveQuietly();
        }

        return is_file($absolutePath) ? $absolutePath : null;
    }

    protected function resolveBusinessForWarehouse(?Business $business, ?Warehouse $warehouse): ?Business
    {
        if (! $business) {
            return null;
        }

        $documentBusiness = clone $business;
        $documentBusiness->direccion_principal = $business->direccion;
        $documentBusiness->direccion_sucursal = null;
        $documentBusiness->direccion_documento = $business->direccion;

        if ($warehouse && filled($warehouse->direccion)) {
            $documentBusiness->direccion_sucursal = $warehouse->direccion;
            $documentBusiness->direccion_documento = $warehouse->direccion;
        }

        return $documentBusiness;
    }

    protected function attemptSunatDispatch(Billing $billing): void
    {
        try {
            app(SunatDispatchService::class)->dispatch($billing);
        } catch (\Throwable $exception) {
            // El comprobante queda registrado aunque el envío a SUNAT falle.
        }
    }

    public function create_cart()
    {
        if (! session()->get('pos') || empty(session()->get('pos')['products'])) {
            $pos = [
                'pos' => [
                    'products' => [],
                    'igv' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                ],
            ];

            session($pos);

            return session()->get('pos');
        }

        $subtotal = 0;
        $igv = 0;

        foreach (session('pos')['products'] as $index => $product) {
            $igvFactor = $this->resolveIgvFactor((int) $product['igv']);
            $precioBase = $igvFactor > 0 ? ((float) $product['precio_venta'] / $igvFactor) : (float) $product['precio_venta'];
            $igvProducto = ((float) $product['precio_venta'] - $precioBase) * (int) $product['cantidad'];
            $igv += $this->redondeado($igvProducto);
            $subtotal += $precioBase * (int) $product['cantidad'];
            session()->put('pos.products.' . $index, $product);
        }

        $total = $subtotal + $igv;

        $pos = [
            'pos' => [
                'products' => session('pos')['products'],
                'igv' => $igv,
                'subtotal' => $subtotal,
                'total' => $total,
            ],
        ];

        session($pos);

        return session()->get('pos');
    }

    public function add_product_cart($id, $cantidad, $precio, $opcion, $idalmacen)
    {
        $product = Product::select(
            'products.*',
            'units.codigo as unidad',
            'stock_products.stock_actual as stock',
            'stock_products.idalmacen as idalmacen',
            'categories.descripcion as categoria'
        )
            ->join('categories', 'products.idcategoria', '=', 'categories.id')
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->join('stock_products', 'products.id', '=', 'stock_products.idproducto')
            ->join('warehouses', 'stock_products.idalmacen', '=', 'warehouses.id')
            ->where('products.id', $id)
            ->where('warehouses.id', $idalmacen)
            ->first();

        if (! $product) {
            return [
                'status' => false,
                'msg' => 'El producto no se encuentra en almacen',
            ];
        }

        if ((int) $opcion === 1 && ((int) $product->stock < (int) $cantidad || (int) $product->stock === 0)) {
            return [
                'status' => false,
                'msg' => 'Producto sin stock para venta',
            ];
        }

        $newProduct = [
            'id' => $product->id,
            'descripcion' => $product->descripcion,
            'idunidad' => $product->idunidad,
            'unidad' => $product->unidad,
            'igv' => $product->igv,
            'idcodigo_igv' => $product->idcodigo_igv,
            'precio_compra' => $product->precio_compra,
            'precio_venta' => $precio,
            'stock' => ((int) $opcion === 1) ? $product->stock : null,
            'opcion' => $opcion,
            'cantidad' => $cantidad,
            'idalmacen' => ((int) $opcion === 1) ? $idalmacen : null,
        ];

        if (empty(session()->get('pos')['products'])) {
            session()->push('pos.products', $newProduct);

            return [
                'status' => true,
                'msg' => '',
            ];
        }

        foreach (session()->get('pos')['products'] as $index => $sessionProduct) {
            if ($id == $sessionProduct['id'] && $sessionProduct['opcion'] == $opcion) {
                if ((int) $opcion === 1 && (int) $sessionProduct['stock'] < ((int) $sessionProduct['cantidad'] + (int) $cantidad)) {
                    return [
                        'status' => false,
                        'msg' => 'Stock insuficiente para agregar más unidades.',
                    ];
                }

                $sessionProduct['cantidad'] = $sessionProduct['cantidad'] + $cantidad;
                session()->put('pos.products.' . $index, $sessionProduct);

                return [
                    'status' => true,
                    'msg' => '',
                ];
            }
        }

        session()->push('pos.products', $newProduct);

        return [
            'status' => true,
            'msg' => '',
        ];
    }

    public function delete_product_cart($id, $opcion)
    {
        if (! session()->get('pos') || empty(session()->get('pos')['products'])) {
            return false;
        }

        foreach (session()->get('pos')['products'] as $index => $product) {
            if ($id == $product['id'] && $opcion == $product['opcion']) {
                session()->forget('pos.products.' . $index);
                return true;
            }
        }

        return false;
    }

    public function update_quantity($id, $cantidad, $precio, $opcion)
    {
        if (empty(session()->get('pos')['products'])) {
            return false;
        }

        foreach (session()->get('pos')['products'] as $index => $product) {
            if ($id == $product['id'] && $opcion == $product['opcion']) {
                if ($product['stock'] !== null && (int) $product['stock'] < (int) $cantidad) {
                    return false;
                }

                $product['cantidad'] = $cantidad;
                $product['precio_venta'] = $precio;
                session()->put('pos.products.' . $index, $product);

                return true;
            }
        }

        return false;
    }

    public function destroy_cart()
    {
        if (! session()->get('pos') || empty(session()->get('pos')['products'])) {
            return false;
        }

        session()->forget('pos');
        return true;
    }
}
