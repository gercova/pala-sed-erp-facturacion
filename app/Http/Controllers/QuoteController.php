<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SendEmailQuote;
use App\Models\Business;
use App\Models\Client;
use App\Models\DetailQuote;
use App\Models\IdentityDocumentType;
use App\Models\IgvTypeAffection;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Serie;
use App\Models\StockProduct;
use App\Models\TypeDocument;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Luecano\NumeroALetras\NumeroALetras;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function index() {
        $query = $this->quotesByCurrentWarehouse();

        return view('admin.quotes.list', [
            'kpi_today_count' => (clone $query)->whereDate('fecha_emision', Carbon::today())->count(),
            'kpi_today_total' => (clone $query)->whereDate('fecha_emision', Carbon::today())->sum('total'),
            'kpi_pending' => (clone $query)->where(function ($query) {
                $query->whereNull('estado')->orWhere('estado', 0);
            })->count(),
            'signo' => $this->signo_pais(),
        ]);
    }

    public function get() {
        $quotes = $this->quotesByCurrentWarehouse()
        ->select('quotes.*', 'clients.nro_documento as dni_ruc', 'clients.nombres as cliente', \DB::raw("CONCAT(quotes.serie, '-', quotes.correlativo) as documento"))
        ->join('clients', 'quotes.idcliente', '=', 'clients.id')
        ->orderBy('id', 'DESC');

        if (request()->has('columns')) {
            $searchValue = request()->input('columns')[0]['search']['value'];
    
            // Separar serie y correlativo solo si hay un guion en el valor de búsqueda
            if (strpos($searchValue, '-') !== false) {
                list($serie, $correlativo) = explode('-', $searchValue);
                $quotes->where('quotes.serie', 'LIKE', '%' . $serie . '%')
                     ->where('quotes.correlativo', 'LIKE', '%' . $correlativo . '%');
            } else {
                // Si no hay guion, buscar en serie y correlativo por separado
                $quotes->where(function ($query) use ($searchValue) {
                    $query->where('quotes.serie', 'LIKE', '%' . $searchValue . '%')
                          ->orWhere('quotes.correlativo', 'LIKE', '%' . $searchValue . '%');
                });
            }
        }
      
        return Datatables()
            ->of($quotes)
            ->editColumn('fecha_emision', function ($quotes) {
                return Carbon::parse((string) $quotes->fecha_emision)->format('Y-m-d');
            })
            ->addColumn('cliente', function ($quotes) {
                $cliente  = $quotes->cliente;
                return $cliente;
            })
            ->addColumn('documento', function ($quotes) {
                $documento  = $quotes->serie . '-' . $quotes->correlativo;
                return $documento;
            })
            ->editColumn('fecha_emision', function ($quotes) {
                return Carbon::parse((string) $quotes->fecha_emision)->format('Y-m-d');
            })
            ->addColumn('acciones', function ($quotes) {
                $id     = $quotes->id;
                $btn    = '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1" style="">
                                <a class="dropdown-item btn-detail-quote" data-id="'.$id.'" href="javascript:void(0);">
                                    <i class="ri-eye-line me-2"></i>
                                    <span> Ver detalle</span>
                                </a>
                                <a class="dropdown-item btn-pdf" data-id="'.$id.'" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4H15V8H19V20H5V4ZM3.9985 2C3.44749 2 3 2.44405 3 2.9918V21.0082C3 21.5447 3.44476 22 3.9934 22H20.0066C20.5551 22 21 21.5489 21 20.9925L20.9997 7L16 2H3.9985ZM10.4999 7.5C10.4999 9.07749 10.0442 10.9373 9.27493 12.6534C8.50287 14.3757 7.46143 15.8502 6.37524 16.7191L7.55464 18.3321C10.4821 16.3804 13.7233 15.0421 16.8585 15.49L17.3162 13.5513C14.6435 12.6604 12.4999 9.98994 12.4999 7.5H10.4999ZM11.0999 13.4716C11.3673 12.8752 11.6042 12.2563 11.8037 11.6285C12.2753 12.3531 12.8553 13.0182 13.5101 13.5953C12.5283 13.7711 11.5665 14.0596 10.6352 14.4276C10.7999 14.1143 10.9551 13.7948 11.0999 13.4716Z"></path></svg>
                                <span> A4</span>
                                </a>
                                <a class="dropdown-item" data-id="'.$id.'" href="'.route("admin.edit_quote", $id).'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit menu-icon"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span> Editar</span>
                                </a>
                                <a class="dropdown-item" href="'.route("admin.convert_quote_to_sale", $id).'">
                                    <i class="ri-shopping-bag-3-line me-2"></i>
                                    <span> Convertir a venta</span>
                                </a>
                            </div>
                            </div>';
                return $btn;
            })
            ->rawColumns(['cliente', 'documento', 'acciones'])
            ->toJson();
    }

    public function create()
    {
        $data['clients']            = Client::get();
        $data['type_documents_p']   = TypeDocument::where('estado', 1)->limit(2)->get();
        $data['typeDocuments']      = IdentityDocumentType::where('estado', 1)->orderBy('descripcion')->get(['id', 'codigo', 'descripcion']);
        $data['modo_pagos']         = PayMode::get();
        $data['products']           = Product::get();
        $data["warehouses"]         = Warehouse::get();
        $data["signo"]              = $this->signo_pais();
        $data["units"]              = Unit::where('estado', 1)->get();
        return view('admin.quotes.create', $data);
    }

    public function get_product(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        $id         = $request->input('id');
        $idalmacen  = $request->input('idalmacen');
        $product    = StockProduct::select(
            'products.codigo_barras',
            'products.codigo_interno',
            'products.descripcion as producto',
            'categories.descripcion as categoria',
            'stock_products.stock_minimo',
            'stock_products.stock_actual',
            'stock_products.idproducto',
            'stock_products.idalmacen',
            'stock_products.precio_compra',
            'stock_products.precio_venta'
        )
            ->join('products', 'stock_products.idproducto', 'products.id')
            ->join('categories', 'products.idcategoria', 'categories.id')
            ->where('stock_products.idproducto', $id)
            ->where('stock_products.idalmacen', $idalmacen)
            ->first();

        return response()->json([
            'status'    => true,
            'product'   => $product
        ]);
    }

    public function get_product_idwarehouse(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $idalmacen          = (int) $request->input('idalmacen');
        $productos          = StockProduct::select(
            'products.codigo_barras',
            'products.codigo_interno',
            'products.descripcion as producto',
            'categories.descripcion as categoria',
            'stock_products.stock_minimo',
            'stock_products.stock_actual',
            'stock_products.idproducto',
            'stock_products.idalmacen',
            'stock_products.precio_compra',
            'stock_products.precio_venta'
        )
            ->join('products', 'stock_products.idproducto', 'products.id')
            ->join('categories', 'products.idcategoria', 'categories.id')
            ->where('stock_products.idalmacen', $idalmacen)
            ->orderBy('stock_products.idproducto', 'desc')
            ->get();

        return response()->json([
            'status'        => true,
            'productos'     => $productos
        ]);
    }

    public function load_clients(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $clients            = Client::orderBy('id', 'ASC')->get();
        return response()->json(['status'  => true, 'clients' => $clients]);
    }

    public function load_cart(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $cart           = $this->create_cart();
        $html_cart      = '';
        $html_totales   = '';
        $contador       = 0;
        $signo          = $this->signo_pais();

        if (!empty($cart['products'])) {
            foreach ($cart['products'] as $i => $product) {
                $contador   = $contador + 1;
                $html_cart .= '<tr>
                                <td class="text-center">' . $contador . '</td>
                                <td>' . $product["descripcion"]. '</td>
                                <td class="text-center">' . $product["unidad"] . '</td>
                                <td class="text-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="' . $product["id"] . '" data-cantidad="' . $product["cantidad"] . '" data-precio_compra="' . $product["precio_compra"] . '" data-precio_venta="' . $product["precio_venta"] . '"><i class="ri-subtract-line me-sm-1"></i></span>
                                        <input type="text" data-id="' . $product["id"] . '" class="quantity-counter text-center form-control input-quantity" value="' . $product["cantidad"] . '" data-precio_venta="' . $product["precio_venta"] . '" data-id="' . $product["id"] . '">
                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="' . $product["id"] . '" data-cantidad="' . $product["cantidad"] . '" data-precio_compra="' . $product["precio_compra"] . '" data-precio_venta="' . $product["precio_venta"] . '"><i class="ri-add-line me-sm-1"></i></span>
                                    </div>
                                </td>
                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center input-update" value="' . number_format($product["precio_venta"], 2, ".", "") . '" data-cantidad="' . $product["cantidad"] . '" data-id="' . $product["id"] . '" name="precio_venta"></td>
                                <td class="text-center">' . number_format(($product["precio_venta"] * $product["cantidad"]), 2, ".", "") . '</td>
                                <td class="text-center"><span data-id="' . $product["id"] . '" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                            </tr>';
            }
        }

        $html_totales   .= '<div class="d-flex justify-content-between mb-2">
                                        <span style="width: 130px !important;">OP. Gravadas:</span>
                                        <span class="fw-medium">' . $signo . number_format(($cart['subtotal']), 2, ".", "") . '</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span style="width: 130px !important;">IGV:</span>
                                        <span class="fw-medium">' . $signo . number_format($cart['igv'], 2, ".", "") . '</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span style="width: 130px !important;">Total:</span>
                                        <span class="fw-medium">' . $signo . number_format($cart['total'], 2, ".", "") . '</span>
                            </div>';

        return response()->json([
            'status'        => true,
            'cart_products' => $cart,
            'html_cart'     => $html_cart,
            'html_totales'  => $html_totales
        ]);
    }

    public function add_product(Request $request)
    {
        if (!$request->ajax()) {
            echo json_encode([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
            return;
        }

        $id             = (int) $request->input('id');
        $producto       = Product::where('id', $id)->first();
        $opcion         = (int) $producto->opcion;
        $cantidad       = (int) $request->input('cantidad');
        $precio         = number_format($request->input('precio'), 2, ".", "");
        $idalmacen      = (int) $request->input('idalmacen');
        $agregar        = $this->add_product_cart($id, $cantidad, $precio, $opcion, $idalmacen);

        if(!$agregar["status"])
        {   
            return response()->json([
                'status'    => false,
                'msg'       => $agregar["msg"],
                'type'      => 'warning'
            ]);
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Registro agregado correctamente',
            'type'      => 'success'
        ]);
    }

    public function delete_product(Request $request)
    {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id             = (int) $request->input('id');
        $producto       = Product::where('id', $id)->first();
        $opcion         = (int) $producto->opcion;
        if(!$this->delete_product_cart($id, $opcion)) {
            return response()->json([
                'status'    => false,
                'msg'       => 'No se pudo eliminar el producto',
                'type'      => 'warning'
            ]);
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Registro eliminado correctamente',
            'type'      => 'success'
        ]);
    }

    public function store_product(Request $request)
    {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id             = (int) $request->input('id');
        $producto       = Product::where('id', $id)->first();
        $opcion         = (int) $producto->opcion;
        $cantidad       = (int) $request->input('cantidad');
        $precio         = number_format($request->input('precio'), 2, ".", "");
        if(!$this->update_quantity($id , $cantidad, $precio, $opcion)) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Stock insuficiente',
                'type'      => 'warning'
            ]);
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Actualizado correctamente',
            'type'      => 'success'
        ]);
    }

    public function save(Request $request)
    {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $fecha_emision          = $request->input('fecha_emision');
        $fecha_vencimiento      = date('Y-m-d');
        $idcliente              = $request->input('dni_ruc');
        $tipo_cambio            = $request->input('tipo_cambio');
        $modo_pago              = $request->input('modo_pago');
        $observaciones          = $request->input('observaciones');
        $cart                   = $this->create_cart(); 
        $ultimo_correlativo     = NULL;
        
        if(empty($idcliente)) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Debe seleccionar el cliente',
                'type'      => 'warning'
            ]);
        }

        if(empty($cart['products'])) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Ingrese al menos 1 producto',
                'type'      => 'warning'
            ]);
        }

        if(Quote::count() == 0)
        $correlativo = str_pad(1, 8, '0', STR_PAD_LEFT);
        else
        $ultimo_correlativo = Quote::latest('id')->first()["correlativo"];
        $correlativo = str_pad($ultimo_correlativo + 1, 8, '0', STR_PAD_LEFT);

        Quote::insert([
            'correlativo'           => $correlativo,
            'fecha_emision'         => $fecha_emision,
            'fecha_vencimiento'     => $fecha_vencimiento,
            'hora'                  => date('H:i:s'),
            'idcliente'             => $idcliente,
            'idpago'                => $modo_pago,
            'subtotal'              => $cart["subtotal"],
            'igv'                   => $cart["igv"],
            'total'                 => $cart["total"],
            'observaciones'         => $observaciones,
            'estado'                => 1,
            'idusuario'             => Auth::user()['id'],
            'idcaja'                => Auth::user()['idcaja'],
        ]);

        $idquote                    = Quote::latest('id')->first()['id'];
        foreach($cart["products"] as $product) {
            DetailQuote::insert([
                'idcotizacion'      => $idquote,
                'idproducto'        => $product['id'],
                'cantidad'          => $product['cantidad'],
                'precio_unitario'   => $product['precio_venta'],
                'precio_total'      => ($product['precio_venta'] * $product['cantidad']),
                'idalmacen'         => $product['idalmacen']
            ]);
        }

        Session::flash('exito', [
            'msg'   => 'Datos actualizados correctamente',
            'id'    => Quote::latest('id')->first()['id']
        ]);
        $this->destroy_cart();
        return response()->json([
            'status'                => true,
            'idcotizacion'          => $idquote
        ]);
    }

    public function print(Request $request)
    {
        if(!$request->ajax()){
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }


        $id                         = $request->input('id');
        $data["quote"]              = $this->quotesByCurrentWarehouse()->where('id', $id)->first();
        if (!$data["quote"]) {
            return response()->json([
                'status'    => false,
                'msg'       => 'La cotizacion no existe en el almacen activo.',
                'type'      => 'warning'
            ], 404);
        }
        $data["moneda"]             = $this->moneda_pais();
        $data["signo"]              = $this->signo_pais();
        $data["business"]           = Business::first();
        $data["client"]             = Client::where('id', $data["quote"]["idcliente"])->first();
        $data["name_quote"]         = mb_strtoupper( $data["client"]->nro_documento . '-' . $data["quote"]["serie"]) . '-' . $data["quote"]["correlativo"];
        $data["logo"]       = Business::first()->logo;
        $data["type_document"]      = TypeDocument::where('id', $data["quote"]["idtipo_comprobante"])->first();
        $formatter                  = new NumeroALetras();
        $data['numero_letras']      = $formatter->toWords($data["quote"]->total, 2);
        $data["detail"]             = DetailQuote::select('detail_quotes.*', 'products.descripcion as producto',
                                    'products.codigo_interno as codigo_interno','units.codigo as unidad', 'products.opcion')
                                    ->join('products', 'detail_quotes.idproducto', '=', 'products.id')
                                    ->join('categories', 'products.idcategoria', '=', 'categories.id')
                                    ->join('units', 'products.idunidad', '=', 'units.id')
                                    ->where('detail_quotes.idcotizacion', $id)
                                    ->get();

        $this->gen_pdf($data, $data["name_quote"]);
        echo json_encode([
            'status'    => true,
            'pdf'       => $data["name_quote"] . '.pdf'
        ]);
    }

    public function gen_pdf($data, $name)
    {
        $pdf    = PDF::loadView('admin.quotes.pdf', $data)->setPaper('A4', 'portrait');
        return $pdf->save(public_path('files/quotes/' . $name . '.pdf'));
    }

    public function print_a4(Request $request)
    {
        return $this->print($request);
    }

    public function edit($id) {
        $data["moneda"]             = $this->moneda_pais();
        $data["signo"]              = $this->signo_pais();
        $data["quote"]              = $this->quotesByCurrentWarehouse()->where('id', $id)->first();
        abort_if(!$data["quote"], 404);
        $data["client"]             = Client::where('id', $data["quote"]->idcliente)->first();
        $data["clients"]            = Client::get();
        $data['typeDocuments']      = IdentityDocumentType::where('estado', 1)->orderBy('descripcion')->get(['id', 'codigo', 'descripcion']);
        $data["modo_pagos"]         = PayMode::get();
        $data["warehouses"]         = Warehouse::get();
        $data["units"]              = Unit::where('estado', 1)->get();
        $data["detalle"]            = DetailQuote::select('detail_quotes.*', 'products.descripcion as producto',
                                    'products.codigo_interno as codigo_interno','units.codigo as unidad',
                                    'products.igv as igv', 'products.opcion as opcion')
                                    ->join('products', 'detail_quotes.idproducto', '=', 'products.id')
                                    ->join('units', 'products.idunidad', '=', 'units.id')
                                    ->where('detail_quotes.idcotizacion', $id)
                                    ->get();

        return view('admin.quotes.edit', $data);
    }

    public function detail(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $quote = $this->quotesByCurrentWarehouse()
            ->select('quotes.*', 'clients.nro_documento as dni_ruc', 'clients.nombres as cliente', 'pay_modes.descripcion as modo_pago')
            ->join('clients', 'quotes.idcliente', '=', 'clients.id')
            ->join('pay_modes', 'quotes.idpago', '=', 'pay_modes.id')
            ->find((int) $request->input('id'));

        if (! $quote) {
            return response()->json([
                'status' => false,
                'msg' => 'La cotizacion no existe.',
                'type' => 'warning',
            ], 404);
        }

        $detail = DetailQuote::query()
            ->select('detail_quotes.*', 'products.descripcion as producto', 'units.codigo as unidad')
            ->join('products', 'detail_quotes.idproducto', '=', 'products.id')
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->where('detail_quotes.idcotizacion', $quote->id)
            ->get();

        return response()->json([
            'status' => true,
            'quote' => $quote,
            'detail' => $detail,
            'fecha_emision' => Carbon::parse((string) $quote->fecha_emision)->format('Y-m-d'),
        ]);
    }

    public function convert_to_sale($id)
    {
        $quote = $this->quotesByCurrentWarehouse()->findOrFail($id);
        $details = DetailQuote::query()
            ->select(
                'detail_quotes.*',
                'products.descripcion',
                'products.idunidad',
                'products.igv',
                'products.idcodigo_igv',
                'products.precio_compra',
                'products.opcion',
                'units.codigo as unidad',
                'stock_products.stock_actual as stock_actual'
            )
            ->join('products', 'detail_quotes.idproducto', '=', 'products.id')
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->leftJoin('stock_products', function ($join) {
                $join->on('detail_quotes.idproducto', '=', 'stock_products.idproducto')
                    ->on('detail_quotes.idalmacen', '=', 'stock_products.idalmacen');
            })
            ->where('detail_quotes.idcotizacion', $quote->id)
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('admin.quotes')->with('message', 'La cotizacion no tiene productos para convertir.');
        }

        session()->forget('pos');

        foreach ($details as $detail) {
            session()->push('pos.products', [
                'id' => $detail->idproducto,
                'descripcion' => $detail->descripcion,
                'idunidad' => $detail->idunidad,
                'unidad' => $detail->unidad,
                'igv' => $detail->igv,
                'idcodigo_igv' => $detail->idcodigo_igv,
                'precio_compra' => $detail->precio_compra,
                'precio_venta' => $detail->precio_unitario,
                'stock' => (int) $detail->opcion === 1 ? (float) ($detail->stock_actual ?? 0) : null,
                'opcion' => $detail->opcion,
                'cantidad' => (float) $detail->cantidad,
                'idalmacen' => (int) $detail->opcion === 1 ? $detail->idalmacen : null,
            ]);
        }

        return redirect()->route('admin.pos.create');
    }

    public function get_product_update(Request $request) {
        if (!$request->ajax()) {
            return response()->update([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }
       
        $id                 = (int) $request->input('id');
        $cantidad           = (int) $request->input('cantidad');
        $precio             = number_format($request->input('precio'), 2, ".", "");
        $idalmacen          = (int) $request->input('idalmacen');
        $stock              = StockProduct::where('idproducto', $id)->where('idalmacen', $idalmacen)->first();
        if((int) $stock->stock_actual <= 0)
        {
            echo json_encode([
                'status'    => false,
                'msg'       => 'Stock insuficiente',
                'type'      => 'warning'
            ]);
            return;
        }
        $producto        = Product::select(
                                    'products.id', 'stock_products.precio_venta','products.descripcion as producto', 
                                    'products.codigo_interno as codigo_interno', 
                                    'units.codigo as unidad', 'products.igv as igv', 'products.opcion as opcion' ,
                                            'stock_products.idalmacen as idalmacen'
                                        )
                                        ->join('units', 'products.idunidad', '=', 'units.id')
                                        ->join('stock_products', 'products.id', 'stock_products.idproducto')
                                        ->join('warehouses', 'stock_products.idalmacen', 'warehouses.id')
                                        ->where('products.id', $id)
                                        ->where('warehouses.id', $idalmacen)
                                        ->first();
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Producto agregado correctamente',
            'type'      => 'success',
            'producto'  => $producto,
            'cantidad'  => $cantidad
        ]);
    }

    public function store_product_update(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id                 = (int) $request->input('id');
        $idalmacen          = (int) $request->input('idalmacen');
        $producto        = Product::select(
                        'products.id', 'stock_products.precio_venta','products.descripcion as producto', 
                        'products.codigo_interno as codigo_interno', 
                        'units.codigo as unidad', 'products.igv as igv', 'products.opcion as opcion',
                                'stock_products.idalmacen as idalmacen'
                            )
                ->join('units', 'products.idunidad', '=', 'units.id')
                ->join('stock_products', 'products.id', 'stock_products.idproducto')
                ->join('warehouses', 'stock_products.idalmacen', 'warehouses.id')
                ->where('products.id', $id)
                ->where('warehouses.id', $idalmacen)
                ->first();

        $cantidad           = (int) $request->input('cantidad');
        $precio             = number_format($request->input('precio'), 2, ".", "");
        return response()->json([
            'status'    => true,
            'cantidad'  => $cantidad,
            'id'        => $id,
            'precio'    => $precio,
            'producto'  => $producto,
            'msg'       => 'Actualizado correctamente',
            'type'      => 'success'
        ]);
    }

    public function gen_update(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }
        
        $idquote                = (int) $request->input('idquote');
        $fecha_emision          = $request->input('fecha_emision');
        $fecha_vencimiento      = $request->input('fecha_emision');
        $idcliente              = $request->input('idcliente');
        $tipo_cambio            = $request->input('tipo_cambio');
        $modo_pago              = $request->input('modo_pago');
        $observaciones          = trim($request->input('observaciones'));
        $products               = json_decode($request->post('productos'));
        $totales                = json_decode($request->post('totales'));
        if (empty($products)) {
            echo json_encode([
                'status'    => false,
                'msg'       => 'Ingrese al menos 1 producto',
                'type'      => 'warning'
            ]);
            return;
        }

        $registros                  = DetailQuote::where('idcotizacion', $idquote)->get();
        $existingIdentifiers        = $registros->pluck('idproducto')->toArray();
        $existingIdalmacenes        = $registros->pluck('idalmacen')->toArray();
        $array_ids                  = [];
        $array_precio               = [];
        $array_cantidad             = [];
        foreach($products as $producto) 
        {
            $array_ids[]            = $producto->idproducto;
            $array_precio[]         = $producto->precio;
            $array_cantidad[]       = $producto->cantidad;
            $array_idalmacen[]      = $producto->idalmacen;
        }
     
        foreach($existingIdentifiers as $i => $id_db)
        {
            if(in_array($id_db, $array_ids)) {
                foreach($products as $producto) {
                    DetailQuote::updateOrCreate([
                        'idcotizacion'      => $idquote,
                        'idproducto'        => $producto->idproducto,
                        'idalmacen'         => $producto->idalmacen
                    ], [
                        'idcotizacion'      => $idquote,
                        'idproducto'        => $producto->idproducto,
                        'cantidad'          => $producto->cantidad,
                        'precio_unitario'   => $producto->precio,
                        'precio_total'      => ($producto->precio * $producto->cantidad),
                        'idalmacen'         => $producto->idalmacen
                    ]);
                }
            } 
            else {
                DetailQuote::where([
                    'idcotizacion'      => $idquote,
                    'idproducto'        => $id_db,
                    'idalmacen'         => $existingIdalmacenes[$i]
                ])->delete();
            }
        }
 
        Quote::where('id', $idquote)->update([
            'fecha_emision'         => $fecha_emision,
            'fecha_vencimiento'     => $fecha_vencimiento,
            'hora'                  => date('H:i:s'),
            'idcliente'             => $idcliente,
            'idpago'                => $modo_pago,
            'subtotal'              => $totales->subtotal,
            'igv'                   => $totales->igv,
            'total'                 => $totales->total,
            'observaciones'         => mb_strtoupper($observaciones),
            'estado'                => 1,
            'idusuario'             => Auth::user()['id'],
            'idcaja'                => Auth::user()['idcaja'],
        ]);

        $id                         = $idquote;
        $data["quote"]              = Quote::where('id', $id)->first();
        $data["moneda"]             = $this->moneda_pais();
        $data["signo"]              = $this->signo_pais();
        $data["quote"]              = Quote::where('id', $id)->first();
        $data["business"]           = Business::where('id', 1)->first();
        $data["client"]             = Client::where('id', $data["quote"]["idcliente"])->first();
        $data["name_quote"]         = mb_strtoupper( $data["client"]->nro_documento . '-' . $data["quote"]["serie"]) . '-' . $data["quote"]["correlativo"];
        $formatter                  = new NumeroALetras();
        $data['numero_letras']      = $formatter->toWords($data["quote"]->total, 2);
        $data["detail"]             = DetailQuote::select('detail_quotes.*', 'products.descripcion as producto',
                                    'products.codigo_interno as codigo_interno','units.codigo as unidad')
                                    ->join('products', 'detail_quotes.idproducto', '=', 'products.id')
                                    ->join('units', 'products.idunidad', '=', 'units.id')
                                    ->where('detail_quotes.idcotizacion', $id)
                                    ->get();

        $this->gen_pdf($data, $data["name_quote"]); 
        Session::flash('exito', [
            'msg'   => 'Datos actualizados correctamente',
            'id'    => $idquote
        ]);
        echo json_encode([
            'status'                => true,
            'pdf'                   => $data["name_quote"] . '.pdf',
            'idcotizacion'          => $idquote
        ]);
    }

    #Functions to cart
    public function create_cart()
    {
        if (!session()->get('quote') || empty(session()->get('quote')['products'])) {
            $quote =
                [
                    'quote' =>
                    [
                        'products'     => [],
                        'igv'          => 0,
                        'subtotal'     => 0,
                        'total'        => 0
                    ]
                ];

            session($quote);
            return session()->get('quote');
        }

        $exonerados = 0;
        $subtotal   = 0;
        $total      = 0;
        $igv        = 0;

        foreach (session('quote')['products'] as $index => $product) {
            $igv__ = (int) $product["igv"];
                $igv_c = null;
            
                switch ($igv__) {
                    case 0:
                        $igv_c = 1; 
                        break;
                    
                    case 10:
                        $igv_c = 1.10;
                        break;
            
                    case 18:
                        $igv_c = 1.18;
                        break;
                }
            
                $precio_base    = (float) $product['precio_venta'] / $igv_c;
                $igv_producto   = ($product['precio_venta'] - $precio_base) * (int) $product['cantidad'];
                $igv            += $this->redondeado($igv_producto);
                $exonerados     += $this->redondeado($precio_base * (int) $product['cantidad']);
                $subtotal       += $precio_base * (int) $product['cantidad'];
                session()->put('quote.products.' . $index, $product);
        }

        $total      = $subtotal + $igv;

        $quote =
            [
                'quote' =>
                [
                    'products'     => session('quote')['products'],
                    'igv'          => $igv,
                    'subtotal'     => $subtotal,
                    'total'        => $total,
                ]
            ];

        session($quote);
        return session()->get('quote');
    }

    public function add_product_cart($id, $cantidad, $precio, $opcion, $idalmacen)
    {
        $product        = Product::select(
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

        if(!$product)
        {
            $data       = [
                'status'    => false,
                'msg'       => 'El producto no se encuentra en almacén'
            ];
            return $data;
        }

        if($opcion == 1)
        {
            if ($product->stock < $cantidad)
            {
                $data       = [
                    'status'    => false,
                    'msg'       => 'Producto sin stock para venta'
                ];
                return $data;
            }
            elseif($product->stock == 0)
            {
                $data       = [
                    'status'    => false,
                    'msg'       => 'Producto sin stock para venta'
                ];
                return $data;
            }
        }

        $new_product    =
        [
            'id'                => $product->id,
            'descripcion'       => $product->descripcion,
            'idunidad'          => $product->idunidad,
            'unidad'            => $product->unidad,
            'igv'               => $product->igv,
            'precio_compra'     => $product->precio_compra,
            'precio_venta'      => $precio,
            'stock'             => ($opcion == 1) ? $product->stock : null,
            'opcion'            => $opcion,
            'cantidad'          => $cantidad,
            'idalmacen'         => ($opcion == 1) ? $idalmacen : null,
        ];

        if (empty(session()->get('quote')['products'])) {
            session()->push('quote.products', $new_product);
            $data       = [
                'status'    => true,
                'msg'       => ''
            ];
            return $data;
        }

        foreach (session()->get('quote')['products'] as $index => $product) {
            if ($id == $product['id'] && $product['opcion'] == $opcion) {
                if ($opcion == 1) {
                    if ($product["stock"] < ($product['cantidad'] + $cantidad)) {
                        $data       = [
                            'status'    => true,
                            'msg'       => ''
                        ];
                        return $data;
                    }
                }
                $product['cantidad'] = $product['cantidad'] + $cantidad;
                session()->put('quote.products.' . $index, $product);
                $data       = [
                    'status'    => true,
                    'msg'       => ''
                ];
                return $data;
            }
        }

        session()->push('quote.products', $new_product);
        $data       = [
            'status'    => true,
            'msg'       => ''
        ];
        return $data;
    }

    public function delete_product_cart($id, $opcion)
    {
        if (!session()->get('quote') || empty(session()->get('quote')['products'])) {
            return false;
        }

        foreach (session()->get('quote')['products'] as $index => $product) {
            if ($id == $product['id'] && $product['opcion'] == $opcion) {
                session()->forget('quote.products.' . $index, $product);
                return true;
            }
        }
    }

    public function update_quantity($id, $cantidad, $precio, $opcion)
    {
        if (empty(session()->get('quote')['products'])) {
            return false;
        }

        foreach (session()->get('quote')['products'] as $index => $product) {
            if ($id == $product['id'] && $product['opcion'] == $opcion) {
                if ($product["stock"] != NULL) {
                    if ($product["stock"] < $cantidad) {
                        return false;
                    } elseif ($product["stock"] == 0) {
                        return false;
                    }
                }
                $product['cantidad']           =  $cantidad;
                $product['precio_venta']       =  $precio;
                session()->put('quote.products.' . $index, $product);
                return true;
            }
        }
    }

    public function destroy_cart() {
        if (!session()->get('quote') || empty(session()->get('quote')['products'])) {
            return false;
        }

        session()->forget('quote');
        return true;
    }
}
