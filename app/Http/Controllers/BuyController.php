<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Buy;
use App\Models\Client;
use App\Models\DetailBuy;
use App\Models\IdentityDocumentType;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\TypeDocument;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Luecano\NumeroALetras\NumeroALetras;

class BuyController extends Controller
{
    public function index()
    {
        return view('admin.buys.list');
    }

    public function get(Request $request)
    {
        $buys = Buy::query()
            ->select(
                'buys.*',
                'clients.nro_documento as nro_documento',
                'clients.nombres as proveedor',
                'type_documents.descripcion as tipo_comprobante',
                DB::raw("CONCAT(buys.serie, '-', buys.correlativo) as documento")
            )
            ->join('clients', 'buys.idproveedor', '=', 'clients.id')
            ->leftJoin('type_documents', 'buys.idtipo_comprobante', '=', 'type_documents.id')
            ->where('buys.idtipo_comprobante', '!=', 6)
            ->when($request->filled('filter_voucher'), function ($query) use ($request) {
                $search = trim((string) $request->input('filter_voucher'));
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('buys.serie', 'like', '%' . $search . '%')
                        ->orWhere('buys.correlativo', 'like', '%' . $search . '%')
                        ->orWhereRaw("CONCAT(buys.serie, '-', buys.correlativo) like ?", ['%' . $search . '%']);
                });
            })
            ->when($request->filled('filter_date'), function ($query) use ($request) {
                $query->whereDate('buys.fecha_emision', $request->input('filter_date'));
            })
            ->when($request->filled('filter_document'), function ($query) use ($request) {
                $query->where('clients.nro_documento', 'like', '%' . trim((string) $request->input('filter_document')) . '%');
            })
            ->when($request->filled('filter_reason'), function ($query) use ($request) {
                $query->where('clients.nombres', 'like', '%' . trim((string) $request->input('filter_reason')) . '%');
            })
            ->when($request->filled('filter_total'), function ($query) use ($request) {
                $query->where('buys.total', 'like', '%' . trim((string) $request->input('filter_total')) . '%');
            })
            ->orderByDesc('buys.id');

        return datatables()
            ->of($buys)
            ->editColumn('documento', function ($buy) {
                return '<div><div class="fw-semibold">' . e((string) $buy->documento) . '</div><div class="small text-muted mt-1">' . e((string) ($buy->tipo_comprobante ?: 'Compra')) . '</div></div>';
            })
            ->editColumn('fecha_emision', function ($buy) {
                return date('d/m/Y', strtotime((string) $buy->fecha_emision));
            })
            ->addColumn('proveedor_info', function ($buy) {
                return '<div><div class="fw-semibold">' . e((string) $buy->proveedor) . '</div><div class="small text-muted mt-1">' . e((string) ($buy->nro_documento ?: 'Sin documento')) . '</div></div>';
            })
            ->editColumn('total', function ($buy) {
                return '<span class="fw-semibold">' . e($this->signo_pais() . number_format((float) $buy->total, 2, '.', '')) . '</span>';
            })
            ->addColumn('estado_compra', function ($buy) {
                return (int) $buy->estado === 1
                    ? '<span class="badge bg-success-subtle text-success">Registrada</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Anulada</span>';
            })
            ->addColumn('acciones', function ($buy) {
                $id = $buy->id;
                return '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                <a class="dropdown-item btn-pdf" data-id="' . $id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4H15V8H19V20H5V4ZM3.9985 2C3.44749 2 3 2.44405 3 2.9918V21.0082C3 21.5447 3.44476 22 3.9934 22H20.0066C20.5551 22 21 21.5489 21 20.9925L20.9997 7L16 2H3.9985ZM10.4999 7.5C10.4999 9.07749 10.0442 10.9373 9.27493 12.6534C8.50287 14.3757 7.46143 15.8502 6.37524 16.7191L7.55464 18.3321C10.4821 16.3804 13.7233 15.0421 16.8585 15.49L17.3162 13.5513C14.6435 12.6604 12.4999 9.98994 12.4999 7.5H10.4999ZM11.0999 13.4716C11.3673 12.8752 11.6042 12.2563 11.8037 11.6285C12.2753 12.3531 12.8553 13.0182 13.5101 13.5953C12.5283 13.7711 11.5665 14.0596 10.6352 14.4276C10.7999 14.1143 10.9551 13.7948 11.0999 13.4716Z"></path></svg>
                                    <span> PDF</span>
                                </a>
                                <a class="dropdown-item btn-confirm" data-id="' . $id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 menu-icon"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    <span> Eliminar</span>
                                </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['documento', 'proveedor_info', 'total', 'estado_compra', 'acciones'])
            ->toJson();
    }

    public function save(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'idtipo_comprobante' => 'required|integer|exists:type_documents,id',
            'serie' => 'required|string|max:10',
            'correlativo' => 'required|string|max:20',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
            'dni_ruc' => 'required|integer|exists:clients,id',
            'modo_pago' => 'required|integer|exists:pay_modes,id',
        ], [
            'idtipo_comprobante.required' => 'Debe seleccionar el tipo de comprobante.',
            'serie.required' => 'Debe ingresar la serie.',
            'correlativo.required' => 'Debe ingresar el número.',
            'fecha_emision.required' => 'Debe ingresar la fecha de emisión.',
            'fecha_vencimiento.required' => 'Debe ingresar la fecha de vencimiento.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser menor a la fecha de emisión.',
            'dni_ruc.required' => 'Debe seleccionar el proveedor.',
            'dni_ruc.exists' => 'El proveedor seleccionado no existe.',
            'modo_pago.required' => 'Debe seleccionar el modo de pago.',
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
        $serie = mb_strtoupper(trim((string) $data['serie']));
        $correlativo = trim((string) $data['correlativo']);
        $cart = $this->create_cart();

        if (empty($cart['products'])) {
            return response()->json([
                'status' => false,
                'msg' => 'Ingrese al menos 1 producto',
                'type' => 'warning',
            ], 422);
        }

        $validBuy = Buy::where('idproveedor', $data['dni_ruc'])
            ->where('serie', $serie)
            ->where('correlativo', $correlativo)
            ->first();

        if (! empty($validBuy)) {
            return response()->json([
                'status' => false,
                'msg' => 'Registro existente con esos datos',
                'type' => 'warning',
            ], 422);
        }

        DB::transaction(function () use ($data, $serie, $correlativo, $cart) {
            $buy = Buy::create([
                'idtipo_comprobante' => $data['idtipo_comprobante'],
                'serie' => $serie,
                'correlativo' => $correlativo,
                'fecha_emision' => $data['fecha_emision'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'hora' => date('H:i:s'),
                'idproveedor' => $data['dni_ruc'],
                'idmoneda' => 1,
                'idpago' => 1,
                'modo_pago' => $data['modo_pago'],
                'anticipo' => '0.00',
                'igv' => $cart['igv'],
                'gratuita' => '0.00',
                'otros_cargos' => '0.00',
                'total' => $cart['total'],
                'observaciones' => '',
                'estado' => 1,
                'idusuario' => Auth::user()['id'],
            ]);

            foreach ($cart['products'] as $product) {
                DetailBuy::create([
                    'idcompra' => $buy->id,
                    'idproducto' => $product['id'],
                    'cantidad' => $product['cantidad'],
                    'descuento' => 0,
                    'igv' => ($product['precio_compra'] * $product['igv']),
                    'id_afectacion_igv' => 1,
                    'precio_unitario' => $product['precio_compra'],
                    'precio_total' => ($product['precio_compra'] * $product['cantidad']),
                    'idalmacen' => $product['idalmacen'],
                ]);

                $registro = StockProduct::where('idproducto', $product['id'])
                    ->where('idalmacen', $product['idalmacen'])
                    ->first();

                if (! $registro) {
                    throw new \RuntimeException('Uno de los productos ya no está registrado en el almacén seleccionado.');
                }

                StockProduct::where('idalmacen', $product['idalmacen'])
                    ->where('idproducto', $product['id'])
                    ->update([
                        'precio_compra' => $product['precio_compra'],
                        'stock_actual' => ((int) $registro->stock_actual) + ((int) $product['cantidad']),
                    ]);
            }

            Session::flash('exito', [
                'msg' => 'Datos guardados correctamente',
                'id' => $buy->id,
            ]);
        });

        $this->destroy_cart();

        return response()->json([
            'status' => true,
        ]);
    }

    public function create()
    {
        $data['type_documents_p'] = TypeDocument::query()
            ->where('estado', 1)
            ->whereIn('codigo', ['03', '01', '02'])
            ->orderByRaw("CASE codigo WHEN '03' THEN 1 WHEN '01' THEN 2 WHEN '02' THEN 3 ELSE 4 END")
            ->get();
        $data['typeDocuments'] = IdentityDocumentType::query()
            ->where('estado', 1)
            ->orderBy('descripcion')
            ->get(['id', 'codigo', 'descripcion']);
        $data['modo_pagos'] = PayMode::orderBy('descripcion')->get();
        $data['warehouses'] = Warehouse::orderBy('descripcion')->get();
        $data['providers'] = Client::orderBy('nombres')->get();
        $data['products'] = Product::where('opcion', '=', 1)->get();

        return view('admin.buys.create', $data);
    }

    public function get_product(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = (int) $request->input('id');
        $idalmacen = (int) $request->input('idalmacen');
        $product = StockProduct::select(
            'products.codigo_barras',
            'products.codigo_interno',
            'products.descripcion as producto',
            'categories.descripcion as categoria',
            'stock_products.stock_minimo',
            'stock_products.stock_actual',
            'stock_products.idproducto',
            'stock_products.idalmacen',
            'stock_products.precio_compra'
        )
            ->join('products', 'stock_products.idproducto', 'products.id')
            ->join('categories', 'products.idcategoria', 'categories.id')
            ->where('stock_products.idproducto', $id)
            ->where('stock_products.idalmacen', $idalmacen)
            ->first();

        if (! $product) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no está disponible en el almacén seleccionado.',
                'type' => 'warning',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'product' => $product,
        ]);
    }

    public function get_product_idwarehouse(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $productos = StockProduct::select(
            'products.opcion',
            'products.codigo_barras',
            'products.codigo_interno',
            'products.descripcion as producto',
            'categories.descripcion as categoria',
            'stock_products.stock_minimo',
            'stock_products.stock_actual',
            'stock_products.idproducto',
            'stock_products.idalmacen',
            'stock_products.precio_compra'
        )
            ->join('products', 'stock_products.idproducto', 'products.id')
            ->join('categories', 'products.idcategoria', 'categories.id')
            ->where('products.opcion', '=', 1)
            ->where('stock_products.idalmacen', $idalmacen)
            ->orderByDesc('stock_products.idproducto')
            ->get();

        return response()->json([
            'status' => true,
            'productos' => $productos,
        ]);
    }

    public function add_product(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:products,id',
            'idalmacen' => 'required|integer|exists:warehouses,id',
            'cantidad' => 'required|numeric|min:1',
            'precio_compra' => 'required|numeric|min:0',
        ], [
            'id.required' => 'Debe seleccionar un producto.',
            'idalmacen.required' => 'Debe seleccionar un almacén.',
            'cantidad.required' => 'Debe ingresar la cantidad.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'precio_compra.required' => 'Debe ingresar el precio de compra.',
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

        if (! $this->add_product_cart((int) $data['id'], (int) $data['idalmacen'], (int) $data['cantidad'], number_format((float) $data['precio_compra'], 2, '.', ''))) {
            return response()->json([
                'status' => false,
                'msg' => 'No se pudo agregar el producto seleccionado',
                'type' => 'warning',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Producto agregado correctamente',
            'type' => 'success',
        ]);
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
                $html_cart .= '<tr>
                                <td class="text-center">' . $contador . '</td>
                                <td>' . e((string) $product['descripcion']) . '</td>
                                <td class="text-center">' . e((string) $product['unidad']) . '</td>
                                <td class="text-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-cart-key="' . e((string) $product['cart_key']) . '" data-cantidad="' . $product['cantidad'] . '" data-precio_compra="' . $product['precio_compra'] . '"><i class="ri-subtract-line me-sm-1"></i></span>
                                        <input type="text" data-cart-key="' . e((string) $product['cart_key']) . '" class="quantity-counter text-center form-control input-quantity" value="' . $product['cantidad'] . '" data-precio_compra="' . $product['precio_compra'] . '">
                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-cart-key="' . e((string) $product['cart_key']) . '" data-cantidad="' . $product['cantidad'] . '" data-precio_compra="' . $product['precio_compra'] . '"><i class="ri-add-line me-sm-1"></i></span>
                                    </div>
                                </td>
                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center input-precio-compra" value="' . number_format((float) $product['precio_compra'], 2, '.', '') . '" data-cantidad="' . $product['cantidad'] . '" data-cart-key="' . e((string) $product['cart_key']) . '" name="precio_compra"></td>
                                <td class="text-center">' . number_format(((float) $product['precio_compra'] * (float) $product['cantidad']), 2, '.', '') . '</td>
                                <td class="text-center"><span data-cart-key="' . e((string) $product['cart_key']) . '" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                            </tr>';
            }
        }

        $html_totales .= '<div class="d-flex justify-content-between mb-2">
                                <span style="width: 130px !important;">OP. Gravadas:</span>
                                <span class="fw-medium">' . $signo . number_format((float) $cart['subtotal'], 2, '.', '') . '</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="width: 130px !important;">IGV:</span>
                                <span class="fw-medium">' . $signo . number_format((float) $cart['igv'], 2, '.', '') . '</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span style="width: 130px !important;">Total:</span>
                                <span class="fw-medium">' . $signo . number_format((float) $cart['total'], 2, '.', '') . '</span>
                            </div>';

        return response()->json([
            'status' => true,
            'cart_products' => $cart,
            'html_cart' => $html_cart,
            'html_totales' => $html_totales,
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

        $cartKey = trim((string) $request->input('cart_key'));
        if ($cartKey === '' || ! $this->delete_product_cart($cartKey)) {
            return response()->json([
                'status' => false,
                'msg' => 'No se pudo eliminar el registro',
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
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

        $validator = Validator::make($request->all(), [
            'cart_key' => 'required|string',
            'cantidad' => 'required|numeric|min:1',
            'precio_compra' => 'required|numeric|min:0',
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

        if (! $this->update_quantity((string) $data['cart_key'], (int) $data['cantidad'], number_format((float) $data['precio_compra'], 2, '.', ''))) {
            return response()->json([
                'status' => false,
                'msg' => 'Algo pasó, intente de nuevo',
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success',
        ]);
    }

    public function print(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = (int) $request->input('id');
        $data['buy'] = Buy::select('buys.*')
            ->where('buys.id', $id)
            ->first();
        $data['moneda'] = $this->moneda_pais();
        $data['signo'] = $this->signo_pais();
        $data['business'] = Business::where('id', 1)->first();
        $data['provider'] = Client::where('id', $data['buy']['idproveedor'])->first();
        $data['name_buy'] = mb_strtoupper($data['provider']->nro_documento . '-' . $data['buy']['serie']) . '-' . $data['buy']['correlativo'];
        $data['type_document'] = TypeDocument::where('id', $data['buy']['idtipo_comprobante'])->first();
        $formatter = new NumeroALetras();
        $data['numero_letras'] = $formatter->toWords($data['buy']->total, 2);
        $data['detail'] = DetailBuy::select(
            'detail_buys.*',
            'products.descripcion as producto',
            'products.codigo_interno as codigo_interno',
            'units.codigo as unidad',
            'categories.descripcion as categoria'
        )
            ->join('products', 'detail_buys.idproducto', '=', 'products.id')
            ->join('categories', 'products.idcategoria', 'categories.id')
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->where('detail_buys.idcompra', $id)
            ->get();

        $this->gen_pdf($data, $data['name_buy']);

        return response()->json([
            'status' => true,
            'pdf' => $data['name_buy'] . '.pdf',
        ]);
    }

    public function test_pdf()
    {
        $pdf = PDF::loadView('admin.buys.test_pdf')->setPaper('A4', 'portrait');
        return $pdf->stream();
    }

    public function gen_pdf($data, $name)
    {
        $pdf = PDF::loadView('admin.buys.pdf', $data)->setPaper('A4', 'portrait');
        return $pdf->save(public_path('files/buys/' . $name . '.pdf'));
    }

    public function delete(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = (int) $request->input('id');
        $buy = Buy::where('id', $id)->first();

        if (! $buy) {
            return response()->json([
                'status' => false,
                'msg' => 'La compra no existe.',
                'type' => 'warning',
            ], 404);
        }

        $detailBuy = DetailBuy::select('detail_buys.*')
            ->join('products', 'detail_buys.idproducto', '=', 'products.id')
            ->where('detail_buys.idcompra', $id)
            ->get();

        DB::transaction(function () use ($detailBuy, $id) {
            foreach ($detailBuy as $item) {
                $idProduct = (int) $item['idproducto'];
                $cantidad = (int) $item['cantidad'];
                $registro = StockProduct::where('idproducto', $idProduct)
                    ->where('idalmacen', $item['idalmacen'])
                    ->first();

                if (! $registro) {
                    continue;
                }

                $nuevoStock = ((int) $registro->stock_actual - $cantidad) <= 0
                    ? 0
                    : ((int) $registro->stock_actual - $cantidad);

                StockProduct::where('idproducto', $idProduct)
                    ->where('idalmacen', $item['idalmacen'])
                    ->update([
                        'stock_actual' => $nuevoStock,
                    ]);
            }

            DetailBuy::where('idcompra', $id)->delete();
            Buy::where('id', $id)->delete();
        });

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
            'type' => 'success',
        ]);
    }

    public function create_cart()
    {
        if (! session()->get('buy') || empty(session()->get('buy')['products'])) {
            $buy = [
                'buy' => [
                    'products' => [],
                    'igv' => 0,
                    'exonerada' => 0,
                    'gravada' => 0,
                    'inafecta' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                ],
            ];

            session($buy);
            return session()->get('buy');
        }

        $subtotal = 0;
        $igv = 0;

        foreach (session('buy')['products'] as $index => $product) {
            $igvValue = (int) $product['igv'];
            $igvFactor = match ($igvValue) {
                10 => 1.10,
                18 => 1.18,
                default => 1,
            };

            $precioBase = (float) $product['precio_compra'] / $igvFactor;
            $igvProducto = ((float) $product['precio_compra'] - $precioBase) * (int) $product['cantidad'];
            $igv += $this->redondeado($igvProducto);
            $subtotal += $precioBase * (int) $product['cantidad'];
            session()->put('buy.products.' . $index, $product);
        }

        $total = $subtotal + $igv;

        $buy = [
            'buy' => [
                'products' => session('buy')['products'],
                'igv' => $igv,
                'subtotal' => $subtotal,
                'total' => $total,
            ],
        ];

        session($buy);
        return session()->get('buy');
    }

    public function add_product_cart($id, $idalmacen, $cantidad, $precio_compra)
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
            ->join('stock_products', 'products.id', 'stock_products.idproducto')
            ->join('warehouses', 'stock_products.idalmacen', 'warehouses.id')
            ->where('products.id', $id)
            ->where('warehouses.id', $idalmacen)
            ->first();

        if (! $product) {
            return false;
        }

        $newProduct = [
            'id' => $product->id,
            'cart_key' => $id . '-' . $idalmacen,
            'codigo_sunat' => $product->codigo_sunat,
            'descripcion' => $product->descripcion,
            'idunidad' => $product->idunidad,
            'unidad' => $product->unidad,
            'idcodigo_igv' => $product->idcodigo_igv,
            'codigo_igv' => $product->codigo_igv,
            'igv' => $product->igv,
            'precio_compra' => $precio_compra,
            'impuesto' => $product->impuesto,
            'idalmacen' => $idalmacen,
            'cantidad' => $cantidad,
        ];

        if (empty(session()->get('buy')['products'])) {
            session()->push('buy.products', $newProduct);
            return true;
        }

        foreach (session()->get('buy')['products'] as $index => $sessionProduct) {
            if (($newProduct['cart_key']) === ($sessionProduct['cart_key'] ?? null)) {
                $sessionProduct['cantidad'] = $sessionProduct['cantidad'] + $cantidad;
                $sessionProduct['precio_compra'] = $precio_compra;
                session()->put('buy.products.' . $index, $sessionProduct);
                return true;
            }
        }

        session()->push('buy.products', $newProduct);
        return true;
    }

    public function delete_product_cart($cartKey)
    {
        if (! session()->get('buy') || empty(session()->get('buy')['products'])) {
            return false;
        }

        foreach (session()->get('buy')['products'] as $index => $product) {
            if ($cartKey === ($product['cart_key'] ?? null)) {
                session()->forget('buy.products.' . $index);
                return true;
            }
        }

        return false;
    }

    public function update_quantity($cartKey, $cantidad, $precio_compra)
    {
        if (empty(session()->get('buy')['products'])) {
            return false;
        }

        foreach (session()->get('buy')['products'] as $index => $product) {
            if ($cartKey === ($product['cart_key'] ?? null)) {
                $product['cantidad'] = $cantidad;
                $product['precio_compra'] = $precio_compra;
                session()->put('buy.products.' . $index, $product);
                return true;
            }
        }

        return false;
    }

    public function destroy_cart()
    {
        if (! session()->get('buy') || empty(session()->get('buy')['products'])) {
            return false;
        }

        session()->forget('buy');
        return true;
    }

    public function load_providers(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $providers = Client::orderBy('nombres')->get();

        return response()->json([
            'status' => true,
            'providers' => $providers,
        ]);
    }
}
