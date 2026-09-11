<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\DetailTransferOrder;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\TransferOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Luecano\NumeroALetras\NumeroALetras;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class TransferOrderController extends Controller
{
    public function index() {
        return view('admin.transfer_orders.list');
    }

    public function get() {
        $transfer_orders    = TransferOrder::query()->select('transfer_orders.*', 'despacho.descripcion as almacen_despacho', 
                            'receptor.descripcion as almacen_receptor')
                            ->join('warehouses as despacho', 'transfer_orders.idalmacen_despacho', '=', 'despacho.id')
                            ->join('warehouses as receptor', 'transfer_orders.idalmacen_receptor', '=', 'receptor.id')
                            ->orderBy('id', 'DESC');

        return Datatables()
                            ->of($transfer_orders)
                            ->addColumn('documento', function ($transfer_orders) {
                                $documento  = $transfer_orders->serie . '-' . $transfer_orders->correlativo;
                                return $documento;
                            })
                            ->addColumn('fecha_de_emision_hide', function ($buys) {
                                $fecha_emision = date('Y-m-d', strtotime($buys->fecha_emision));
                                return $fecha_emision;
                            })
                            ->addColumn('fecha_de_emision', function ($transfer_orders) {
                                $fecha_emision = date('d-m-Y', strtotime($transfer_orders->fecha_emision));
                                return $fecha_emision;
                            })
                            ->addColumn('estado_compra', function ($transfer_orders) {
                                $estado    = $transfer_orders->estado;
                                $btn    = '';
                                switch ($estado) {
                                    case '0':
                                        $btn .= '<span class="badge text-white" style="background-color: rgb(108, 117, 125);">Registrado</span>';
                                        break;
                
                                    case '1':
                                        $btn .= '<span class="badge bg-success text-white">Aceptado</span>';
                                        break;
                
                                    case '2':
                                        $btn .= '<span class="badge bg-danger text-white">Anulado</span>';
                                        break;
                                }
                                return $btn;
                            })
                            ->addColumn('acciones', function ($transfer_orders) {
                                $id         = $transfer_orders->id;
                                $disabled   = ($transfer_orders->estado == 2) ? 'disabled' : '';
                                $status     = ($transfer_orders->estado == 1) ? 'd-none' : '';
                                $status_a   = ($transfer_orders->estado == 1) ? '' : 'd-none';
                                $btn        = '<div class="dropdown">
                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="' . $disabled . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                                </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1" style="">
                                <a class="dropdown-item btn-confirm-transfer '.$status.'"  data-id="'.$id.'" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 4H21C21.5523 4 22 4.44772 22 5V12H20V6H6V9L1 5L6 1V4ZM18 20H3C2.44772 20 2 19.5523 2 19V12H4V18H18V15L23 19L18 23V20Z"></path></svg>
                                <span> Confirmar </span>
                                </a>
                                <a class="dropdown-item btn-print '.$status_a.'" data-id="'.$id.'" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4H15V8H19V20H5V4ZM3.9985 2C3.44749 2 3 2.44405 3 2.9918V21.0082C3 21.5447 3.44476 22 3.9934 22H20.0066C20.5551 22 21 21.5489 21 20.9925L20.9997 7L16 2H3.9985ZM10.4999 7.5C10.4999 9.07749 10.0442 10.9373 9.27493 12.6534C8.50287 14.3757 7.46143 15.8502 6.37524 16.7191L7.55464 18.3321C10.4821 16.3804 13.7233 15.0421 16.8585 15.49L17.3162 13.5513C14.6435 12.6604 12.4999 9.98994 12.4999 7.5H10.4999ZM11.0999 13.4716C11.3673 12.8752 11.6042 12.2563 11.8037 11.6285C12.2753 12.3531 12.8553 13.0182 13.5101 13.5953C12.5283 13.7711 11.5665 14.0596 10.6352 14.4276C10.7999 14.1143 10.9551 13.7948 11.0999 13.4716Z"></path></svg>
                                <span> PDF</span>
                                </a>
                                <a class="dropdown-item btn-confirm '.$status.'" data-id="' . $id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"  class="menu-icon" fill="currentColor"><path d="M16.9057 5.68009L5.68009 16.9057C4.62644 15.5506 4 13.8491 4 12C4 7.58172 7.58172 4 12 4C13.8491 4 15.5506 4.62644 16.9057 5.68009ZM7.0943 18.3199L18.3199 7.0943C19.3736 8.44939 20 10.1509 20 12C20 16.4183 16.4183 20 12 20C10.1509 20 8.44939 19.3736 7.0943 18.3199ZM12 2C6.47715 2 2 6.47715 2 12C2 17.5223 6.47771 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47771 17.5223 2 12 2Z"></path></svg>
                                    <span> Anular</span>
                                </a>
                            </div>
                            </div>';
                                return $btn;
                            })
                            ->rawColumns(['proveedor', 'documento', 'fecha_de_emision_hide' ,'fecha_de_emision', 'estado_compra', 'acciones'])
                            ->make(true);
    }

    public function detail(Request $request) {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }
        
        $id                 = $request->input('id');
        $transfer           = TransferOrder::select('transfer_orders.*', 'despacho.descripcion as almacen_despacho',
                            'receptor.descripcion as almacen_receptor')
                            ->join('warehouses as despacho', 'transfer_orders.idalmacen_despacho', '=', 'despacho.id')
                            ->join('warehouses as receptor', 'transfer_orders.idalmacen_receptor', '=', 'receptor.id')
                            ->where('transfer_orders.id', $id)
                            ->first();
        
        $fecha_emision      = date('d-m-Y', strtotime($transfer->fecha_emision));
        $fecha_vencimiento  = date('d-m-Y', strtotime($transfer->fecha_vencimiento));

        $detail_transfer    = DetailTransferOrder::select('detail_transfer_orders.*', 'products.codigo_interno as codigo_interno', 
                            'products.descripcion as producto', 'categories.descripcion as categoria')
                            ->join('products', 'detail_transfer_orders.idproducto', 'products.id')
                            ->join('categories', 'products.idcategoria', 'categories.id')
                            ->where('detail_transfer_orders.idorden_traslado', $id)
                            ->get();

        return response()->json([
            'status'            => true,
            'transfer'          => $transfer,
            'detail_transfer'   => $detail_transfer,
            'fecha_emision'     => $fecha_emision,
            'fecha_vencimiento' => $fecha_vencimiento
        ]);
    }

    public function anulled(Request $request) {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id                 = $request->input('id');
        TransferOrder::where('id', $id)->update([
            'estado'        => 2
        ]);
        return response()->json([
            'status'    => true,
            'msg'       => 'Orden anulada correctamente',
            'type'      => 'success'
        ]);
    }

    public function move(Request $request) {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id                 = $request->input('idtransfer');
        $transfer           = TransferOrder::select('transfer_orders.*', 'despacho.descripcion as almacen_despacho',
                            'receptor.descripcion as almacen_receptor')
                            ->join('warehouses as despacho', 'transfer_orders.idalmacen_despacho', '=', 'despacho.id')
                            ->join('warehouses as receptor', 'transfer_orders.idalmacen_receptor', '=', 'receptor.id')
                            ->where('transfer_orders.id', $id)
                            ->first();

        $detail_transfer    = DetailTransferOrder::select('detail_transfer_orders.*', 'products.codigo_interno as codigo_interno', 
                            'products.descripcion as producto', 'categories.descripcion as categoria', 'products.precio_compra',
                            'products.precio_venta')
                            ->join('products', 'detail_transfer_orders.idproducto', 'products.id')
                            ->join('categories', 'products.idcategoria', 'categories.id')
                            ->where('detail_transfer_orders.idorden_traslado', $id)
                            ->get();
        // Aumentar la cantidad del almacen receptor y dismunuir de despacho
        foreach($detail_transfer as $i => $product)
        {
            $cantidad_despacho              = StockProduct::where('idproducto', $product['idproducto'])
                                            ->where('idalmacen', $transfer->idalmacen_despacho)
                                            ->first()['stock_actual'];



            $producto_almacen               = StockProduct::where('idproducto', $product['idproducto'])
                                            ->where('idalmacen', $transfer->idalmacen_receptor)
                                            ->first();
                                   
            $cantidad_receptor              = (empty($producto_almacen)) ? 0 : $producto_almacen['stock_actual'];
            $precio_compra                  = (empty($producto_almacen)) ? 0 : $product["precio_compra"];
            $precio_venta                   = (empty($producto_almacen)) ? 0 : $product["precio_venta"];
            $stock_minimo                   = (empty($producto_almacen)) ? 5 : $producto_almacen["stock_minimo"];
            $fecha_registro                 = (empty($producto_almacen)) ? date('Y-m-d') : $producto_almacen["fecha_registro"];
            StockProduct::where('idproducto', $product["idproducto"])
                        ->where('idalmacen', $transfer->idalmacen_despacho)
                        ->update([
                            'stock_actual'  => ((int) $cantidad_despacho - (int) $product['cantidad']),
                        ]);

            StockProduct::updateOrCreate([
                            'idproducto'  => $product["idproducto"],
                            'idalmacen'   => $transfer->idalmacen_receptor,
                        ], [
                            'stock_actual'      => ((int) $cantidad_receptor + (int) $product['cantidad']),
                            'idalmacen'         => $transfer->idalmacen_receptor,
                            'precio_compra'     => $precio_compra,
                            'precio_venta'      => $precio_venta,
                            'stock_minimo'      => $stock_minimo,
                            'fecha_registro'    => $fecha_registro,
                            'stock_entrada'     => ((int) $cantidad_receptor + (int) $product['cantidad'])
                        ]);
        }

        TransferOrder::where('id', $id)->update([
            'estado'    => 1
        ]);

        // Gen PDF
        $data['business']           = Business::first();
        $data['transfer']           = TransferOrder::select('transfer_orders.*', 'despacho.descripcion as almacen_despacho',
                                    'receptor.descripcion as almacen_receptor', 'users.nombres as usuario_solicita')
                                    ->join('warehouses as despacho', 'transfer_orders.idalmacen_despacho', '=', 'despacho.id')
                                    ->join('warehouses as receptor', 'transfer_orders.idalmacen_receptor', '=', 'receptor.id')
                                    ->join('users', 'transfer_orders.idusuario', 'users.id')
                                    ->where('transfer_orders.id', $id)
                                    ->first();

        $data['detalle']            = DetailTransferOrder::select('detail_transfer_orders.*', 'products.codigo_interno as codigo_interno', 
                                    'products.descripcion as producto', 'units.codigo as unidad', 'categories.descripcion as categoria')
                                    ->join('products', 'detail_transfer_orders.idproducto', 'products.id')
                                    ->join('categories', 'products.idcategoria', 'categories.id')
                                    ->join('units', 'products.idunidad', '=', 'units.id')
                                    ->where('detail_transfer_orders.idorden_traslado', $id)
                                    ->get();
        $data["logo"]       = Business::first()->logo;

        $name                       = $data["business"]->ruc . '-' . $data["transfer"]->serie . '-' . $data["transfer"]->correlativo . '.pdf';
        $pdf    = PDF::loadView('admin.transfer_orders.pdf', $data)->setPaper('A4', 'portrait');
        $pdf->save(public_path('files/transfer-orders/' . $name));

        return response()->json([
            'status'    => true,
            'pdf'       => $name
        ]);
    }

    public function print(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }


        $id                         = $request->input('id');
        $data['business']           = Business::first();
        $data['transfer']           = TransferOrder::select('transfer_orders.*', 'despacho.descripcion as almacen_despacho',
                                    'receptor.descripcion as almacen_receptor', 'users.nombres as usuario_solicita')
                                    ->join('warehouses as despacho', 'transfer_orders.idalmacen_despacho', '=', 'despacho.id')
                                    ->join('warehouses as receptor', 'transfer_orders.idalmacen_receptor', '=', 'receptor.id')
                                    ->join('users', 'transfer_orders.idusuario', 'users.id')
                                    ->where('transfer_orders.id', $id)
                                    ->first();

        $data['detalle']            = DetailTransferOrder::select('detail_transfer_orders.*', 'products.codigo_interno as codigo_interno', 
                                    'products.descripcion as producto', 'units.codigo as unidad', 'categories.descripcion as categoria')
                                    ->join('products', 'detail_transfer_orders.idproducto', 'products.id')
                                    ->join('categories', 'products.idcategoria', 'categories.id')
                                    ->join('units', 'products.idunidad', '=', 'units.id')
                                    ->where('detail_transfer_orders.idorden_traslado', $id)
                                    ->get();
        $name                       = $data["business"]->ruc . '-' . $data["transfer"]->serie . '-' . $data["transfer"]->correlativo . '.pdf';
        $pdf    = PDF::loadView('admin.transfer_orders.pdf', $data)->setPaper('A4', 'portrait');
        $pdf->save(public_path('files/transfer-orders/' . $name));

        return response()->json([
            'status'    => true,
            'pdf'       => $name
        ]);
    }

    public function create() {
        $data["warehouses"]     = Warehouse::get();
        return view('admin.transfer_orders.create', $data);
    }

    public function load_serie(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
            return;
        }

        $valid_transfer         = TransferOrder::count();
        $serie                  = 'OT01';
        if($valid_transfer == 0) {
            $correlativo        = str_pad(1, 8, '0', STR_PAD_LEFT);
        } 
        else {
            $last_transfer      = TransferOrder::latest('id')->first();
            $last_correlative   = (int) $last_transfer->correlativo + 1;  
            $correlativo        = str_pad($last_correlative, 8, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'status'        => true,
            'serie'         => $serie,
            'correlativo'   => $correlativo
        ]);
    }

    public function load_warehouse_office(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
            return;
        }

        $idwarehouse_office     = $request->input('idwarehouse_office');
        $receivings_office      = Warehouse::where('id', '!=', $idwarehouse_office)->get();
        return response()->json([
            'status'            => true,
            'receivings_office' => $receivings_office
        ]);
    }

    public function load_warehouse_dispatch(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        $id                 = $request->input('id');
        $receivings_office  = Warehouse::where('id', '!=', $id)->get();
        return response()->json([
            'status'            => true,
            'receivings_office' => $receivings_office
        ]);
    }

    public function search_product(Request $request)
    {
        if(!$request->ajax())
        {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        $value              = trim($request->input('value'));
        $products = Product::select("products.*", 
                            'categories.descripcion as categoria')
                    ->join('categories', 'products.idcategoria', 'categories.id')
                    ->where('products.opcion', '!=', 2)
                    ->where(function ($query) use ($value) {
                        $query->where('products.descripcion', 'like', "%$value%")
                            ->orWhere('codigo_interno', 'like', "%$value%")
                            ->orWhere('categories.descripcion', 'like', "%$value%");
                    })
                    ->get();

        $html_products      = '';
        $scroll_y           = count($products) >= 8 ? 'el-table--scrollable-y  el-table--enable-row-transition' : '';
        if(count($products) >= 1)
        {
            foreach($products as $product)
            {
                $codigo_interno = empty($product["codigo_interno"]) ? '-' : $product["codigo_interno"];
                $html_products .= '<tr class="el-table__row btn__select__product" data-id="'. $product["id"] .'" style="cursor: pointer !important;"> <td rowspan="1" colspan="1" class="el-table__cell">
                                            <div class="cell text-left">'. $codigo_interno .'</div>
                                        </td>
                                        <td rowspan="1" colspan="1" class="el-table__cell">
                                            <div class="cell" style="text-align: left;">'. $product["descripcion"] . '</div>
                                        </td>
                                        <td rowspan="1" colspan="1" class="el-table__cell">
                                            <div class="cell" style="text-align: right;">'. number_format($product["precio_venta"], 2) .'</div>
                                        </td>
                </tr>';
            }
        }

        return response()->json([
            'status'        => true,
            'products'      => $products,
            'html_products' => $html_products,
            'scroll_y'      => $scroll_y,
            'quantity'      => count($products)
        ]);
    }

    public function detail_product(Request $request) {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id                 = (int) $request->input('id');
        $product            = Product::select('products.*', 'stock_products.precio_venta')
                            ->join('stock_products', 'products.id', 'stock_products.idproducto')
                            ->where('stock_products.idproducto', $id)
                            ->first();
        $warehouses         = [];
        $detail_stocks      = StockProduct::where('idproducto', $id)->get();

        foreach($detail_stocks as $stock) {
            $warehouses[]   = Warehouse::where('id', $stock["idalmacen"])->first();
        }

        return response()->json([
            'status'        => true,
            'product'       => $product,
            'warehouses'    => $warehouses,
            'detail_stocks' => $detail_stocks
        ]);
    }

    # Cart
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
                                <td>' . $product["descripcion"] .'</td>
                                <td class="text-center">' . $product["unidad"] . '</td>
                                <td class="text-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="' . $product["id"] . '" data-cantidad="' . $product["cantidad"] . '" data-precio="' . $product["precio_venta"] . '"><i class="ri-subtract-line me-sm-1"></i></span>
                                        <input type="text" data-id="' . $product["id"] . '" class="quantity-counter text-center form-control" value="' . $product["cantidad"] . '">
                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="' . $product["id"] . '" data-cantidad="' . $product["cantidad"] . '" data-precio="' . $product["precio_venta"] . '"><i class="ri-add-line me-sm-1"></i></span>
                                    </div>
                                </td>
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

        echo json_encode([
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
        $idproducto         = (int) $request->input('idproducto');
        $idalmacen_despacho = (int) $request->input('idalmacen_despacho');
        $cantidad           = (int) $request->input('cantidad');

        $agregar            = $this->add_product_cart($idproducto, $cantidad, $idalmacen_despacho);
        if(!$agregar["status"]) {   
            return response()->json([
                'status'    => false,
                'msg'       => $agregar["msg"],
                'type'      => 'warning'
            ]);
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Datos agregados correctamente',
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
        if(!$this->delete_product_cart($id)) {
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
        $cantidad       = (int) $request->input('cantidad');
        if(!$this->update_quantity($id , $cantidad)) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Producto sin stock para traslado',
                'type'      => 'warning'
            ]);
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Datos actualizados correctamente',
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

        $serie                  = trim((string) $request->input('serie'));
        $correlativo            = trim((string) $request->input('correlativo'));
        $fecha_emision          = $request->input('fecha_emision');
        $fecha_vencimiento      = $request->input('fecha_vencimiento');
        $idalmacen_despacho     = (int) $request->input('almacen_despacho');
        $idalmacen_receptor     = (int) $request->input('almacen_receptor');
        $observaciones          = trim((string) $request->input('observaciones'));
        $cart                   = $this->create_cart();

        if ($serie === '' || $correlativo === '') {
            return response()->json([
                'status'    => false,
                'msg'       => 'Debe completar la serie y el correlativo.',
                'type'      => 'warning'
            ]);
        }

        if (empty($fecha_emision) || empty($fecha_vencimiento)) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Debe completar las fechas del traslado.',
                'type'      => 'warning'
            ]);
        }

        if ($idalmacen_despacho <= 0 || $idalmacen_receptor <= 0) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Debe seleccionar el almacén de despacho y el almacén destino.',
                'type'      => 'warning'
            ]);
        }

        if ($idalmacen_despacho === $idalmacen_receptor) {
            return response()->json([
                'status'    => false,
                'msg'       => 'El almacén de despacho y destino deben ser diferentes.',
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

        $existingTransfer = TransferOrder::query()
            ->where('serie', $serie)
            ->where('correlativo', $correlativo)
            ->first();

        if ($existingTransfer) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Ya existe una orden de traslado con esa serie y correlativo.',
                'type'      => 'warning'
            ]);
        }

        $transferId = DB::transaction(function () use (
            $serie,
            $correlativo,
            $fecha_emision,
            $fecha_vencimiento,
            $idalmacen_despacho,
            $idalmacen_receptor,
            $observaciones,
            $cart
        ) {
            $transfer = TransferOrder::create([
                'serie'                 => $serie,
                'correlativo'           => $correlativo,
                'fecha_emision'         => $fecha_emision,
                'fecha_vencimiento'     => $fecha_vencimiento,
                'hora'                  => date('H:i:s'),
                'idalmacen_despacho'    => $idalmacen_despacho,
                'idalmacen_receptor'    => $idalmacen_receptor,
                'observaciones'         => $observaciones,
                'idusuario'             => Auth::user()['id'],
                'estado'                => 0,
                'idalmacen'             => $idalmacen_despacho,
            ]);

            foreach($cart["products"] as $product) {
                DetailTransferOrder::create([
                    'idorden_traslado'  => $transfer->id,
                    'idproducto'        => $product['id'],
                    'cantidad'          => $product['cantidad'],
                ]);
            }

            return $transfer->id;
        });

        Session::flash('exito', [
            'msg'   => 'Orden de traslado registrada correctamente',
            'id'    => $transferId
        ]);

        $this->destroy_cart();

        return response()->json([
            'status'        => true,
            'msg'           => 'Orden de traslado registrada correctamente',
            'type'          => 'success',
            'idtransfer'    => $transferId
        ]);
    }

    public function get_product_idwarehouse(Request $request)
    {
        if (!$request->ajax()) {
            return response()-json([
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


        ## Functions to cart
        public function create_cart()
        {
            if (!session()->get('transfer') || empty(session()->get('transfer')['products'])) {
                $transfer =
                    [
                        'transfer' =>
                        [
                            'products'     => [],
                            'igv'          => 0,
                            'subtotal'     => 0,
                            'total'        => 0
                        ]
                    ];
    
                session($transfer);
                return session()->get('transfer');
            }
    
            $exonerados = 0;
            $subtotal   = 0;
            $total      = 0;
            $igv        = 0;
    
            foreach (session('transfer')['products'] as $index => $product) {

                $igv__ = (int) $product["igv"];
                $igv_c = null;
            
                switch ($igv__) {
                    case 0:
                        $igv_c = 1; // Evita la división por 0
                        break;
                    
                    case 10:
                        $igv_c = 1.10;
                        break;
            
                    case 18:
                        $igv_c = 1.18;
                        break;
                }
            
                // Calcular base imponible (precio sin IGV)
                $precio_base = (float) $product['precio_venta'] / $igv_c;
            
                // Calcular IGV
                $igv_producto = ($product['precio_venta'] - $precio_base) * (int) $product['cantidad'];
                $igv += $this->redondeado($igv_producto);
            
                // Calcular subtotal (exonerado si aplica)
                $exonerados += $this->redondeado($precio_base * (int) $product['cantidad']);
            
                // Acumular subtotal correctamente
                $subtotal += $precio_base * (int) $product['cantidad'];
            
                // Guardar cambios en la sesión
                session()->put('transfer.products.' . $index, $product);
            }
            
    
            $total      = $subtotal + $igv;
    
            $transfer =
                [
                    'transfer' =>
                    [
                        'products'     => session('transfer')['products'],
                        'igv'          => $igv,
                        'subtotal'     => $subtotal,
                        'total'        => $total,
                    ]
                ];
    
            session($transfer);
            return session()->get('transfer');
        }
    
        public function add_product_cart($id, $cantidad, $idalmacen_despacho)
        {
            $product        = Product::select(
                'products.*',
                'units.codigo as unidad',
                'stock_products.stock_actual as stock',
                'stock_products.idalmacen as idalmacen'
            )
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->join('stock_products', 'products.id', 'stock_products.idproducto')
            ->join('warehouses', 'stock_products.idalmacen', 'warehouses.id')
            ->where('products.id', $id)
            ->where('warehouses.id', $idalmacen_despacho)
            ->first();
    
            if(!$product) {
                $data           = [
                    'status'    => false,
                    'msg'       => 'El producto no se encuentra en almacén'
                ];
                return $data;
            }
    
            if ($product->stock < $cantidad) {
                $data       = [
                    'status'    => false,
                    'msg'       => 'Producto sin stock para traslado'
                ];
                return $data;
            }
            elseif($product->stock == 0) {
                $data     = [
                    'status'    => false,
                    'msg'       => 'Producto sin stock para traslado'
                ];
                return $data;
            }
    
            $new_product         =  [
                'id'                => $product->id,
                'descripcion'       => $product->descripcion,
                'idunidad'          => $product->idunidad,
                'unidad'            => $product->unidad,
                'igv'               => $product->igv,
                'precio_compra'     => $product->precio_compra,
                'precio_venta'      => $product->precio_venta,
                'stock'             => $product->stock,
                'cantidad'          => $cantidad,
                'idalmacen'         => $product->idalmacen,
            ];
    
            if (empty(session()->get('transfer')['products'])) {
                session()->push('transfer.products', $new_product);
                $data       = [
                    'status'    => true,
                    'msg'       => ''
                ];
                return $data;
            }
    
            foreach (session()->get('transfer')['products'] as $index => $product) {
                if ($id == $product['id']) {
                    if ($product["stock"] < ($product['cantidad'] + $cantidad)) {
                        $data       = [
                            'status'    => false,
                            'msg'       => 'Stock insuficiente'
                        ];
                        return $data;
                    }
                    $product['cantidad'] = $product['cantidad'] + $cantidad;
                    session()->put('transfer.products.' . $index, $product);
                    $data       = [
                        'status'    => true,
                        'msg'       => ''
                    ];
                    return $data;
                }
            }
    
            session()->push('transfer.products', $new_product);
            $data       = [
                'status'    => true,
                'msg'       => ''
            ];
            return $data;
        }
    
        public function delete_product_cart($id)
        {
            if (!session()->get('transfer') || empty(session()->get('transfer')['products'])) {
                return false;
            }
    
            foreach (session()->get('transfer')['products'] as $index => $product) {
                if ($id == $product['id']) {
                    session()->forget('transfer.products.' . $index, $product);
                    return true;
                }
            }
        }
    
        public function update_quantity($id, $cantidad)
        {
            if (empty(session()->get('transfer')['products'])) {
                return false;
            }
    
            foreach (session()->get('transfer')['products'] as $index => $product) {
                if ($id == $product['id']) {
                    if ($product["stock"] < $cantidad) {
                        return false;
                    } elseif ($product["stock"] == 0) {
                        return false;
                    }
                    
                    $product['cantidad']           =  $cantidad;
                    session()->put('transfer.products.' . $index, $product);
                    return true;
                }
            }
        }
    
        public function destroy_cart()
        {
            if (!session()->get('transfer') || empty(session()->get('transfer')['products'])) {
                return false;
            }
    
            session()->forget('transfer');
            return true;
        }
}
