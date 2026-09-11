<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArchingCash;
use App\Models\Business;
use App\Models\Client;
use App\Models\DetailPayment;
use App\Models\DetailSaleNote;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\Serie;
use App\Models\StockProduct;
use App\Models\TypeDocument;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\SaleNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Luecano\NumeroALetras\NumeroALetras;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SaleNoteController extends Controller
{
    public function index()
    {
        $data = [];
        $query = $this->saleNotesByCurrentWarehouse();
        $data["kpi_today_count"] = (clone $query)->whereDate('fecha_emision', Carbon::today())->count();
        $data["kpi_today_total"] = (clone $query)->whereDate('fecha_emision', Carbon::today())->sum('total');
        $data["kpi_pending"]     = (clone $query)->where('estado', '0')->count();
        $data["signo"]           = $this->signo_pais();
        return view('admin.sale_notes.list', $data);
    }

    public function get()
    {
        $sale_notes = $this->saleNotesByCurrentWarehouse()
            ->select('sale_notes.*', 'clients.nro_documento as dni_ruc', 'clients.nombres as cliente', \DB::raw("CONCAT(sale_notes.serie, '-', sale_notes.correlativo) as documento"))
            ->join('clients', 'sale_notes.idcliente', '=', 'clients.id')
            ->orderBy('id', 'DESC');

        if (request()->has('columns')) {
            $searchValue = request()->input('columns')[0]['search']['value'];
            if (strpos($searchValue, '-') !== false) {
                list($serie, $correlativo) = explode('-', $searchValue);
                $sale_notes->where('sale_notes.serie', 'LIKE', '%' . $serie . '%')
                    ->where('sale_notes.correlativo', 'LIKE', '%' . $correlativo . '%');
            } else {
                $sale_notes->where(function ($query) use ($searchValue) {
                    $query->where('sale_notes.serie', 'LIKE', '%' . $searchValue . '%')
                        ->orWhere('sale_notes.correlativo', 'LIKE', '%' . $searchValue . '%');
                });
            }
        }

        return Datatables()
            ->of($sale_notes)
            ->addColumn('cliente', function ($sale_notes) {
                $cliente  = $sale_notes->cliente;
                return $cliente;
            })
            ->addColumn('documento', function ($sale_notes) {
                $documento  = $sale_notes->serie . '-' . $sale_notes->correlativo;
                return $documento;
            })
            ->addColumn('estado', function ($sale_notes) {
                $estado    = $sale_notes->estado;
                $btn    = '';
                switch ($estado) {
                    case '0':
                        $btn .= '<span class="badge text-white" style="background-color: rgb(108, 117, 125);">Registrado</span>';
                        break;

                    case '1':
                        $btn .= '<span class="badge bg-success text-white">Pagado</span>';
                        break;

                    case '2':
                        $btn .= '<span class="badge bg-danger text-white">Anulado</span>';
                        break;
                }
                return $btn;
            })
            ->addColumn('acciones', function ($sale_notes) {
                $id     = $sale_notes->id;
                $btn    = '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1" style="">
                                <a class="dropdown-item btn-pdf" data-id="' . $id . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4H15V8H19V20H5V4ZM3.9985 2C3.44749 2 3 2.44405 3 2.9918V21.0082C3 21.5447 3.44476 22 3.9934 22H20.0066C20.5551 22 21 21.5489 21 20.9925L20.9997 7L16 2H3.9985ZM10.4999 7.5C10.4999 9.07749 10.0442 10.9373 9.27493 12.6534C8.50287 14.3757 7.46143 15.8502 6.37524 16.7191L7.55464 18.3321C10.4821 16.3804 13.7233 15.0421 16.8585 15.49L17.3162 13.5513C14.6435 12.6604 12.4999 9.98994 12.4999 7.5H10.4999ZM11.0999 13.4716C11.3673 12.8752 11.6042 12.2563 11.8037 11.6285C12.2753 12.3531 12.8553 13.0182 13.5101 13.5953C12.5283 13.7711 11.5665 14.0596 10.6352 14.4276C10.7999 14.1143 10.9551 13.7948 11.0999 13.4716Z"></path></svg>
                                <span> PDF</span>
                                </a>
                                <a class="dropdown-item btn-ticket" data-id="' . $id . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 20V4H5V20H19ZM7 6H11V10H7V6ZM7 12H17V14H7V12ZM7 16H17V18H7V16ZM13 7H17V9H13V7Z"></path></svg>
                                <span> Ticket</span>
                                </a>
                                <a class="dropdown-item btn-confirm" data-id="' . $id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"  class="menu-icon" fill="currentColor"><path d="M16.9057 5.68009L5.68009 16.9057C4.62644 15.5506 4 13.8491 4 12C4 7.58172 7.58172 4 12 4C13.8491 4 15.5506 4.62644 16.9057 5.68009ZM7.0943 18.3199L18.3199 7.0943C19.3736 8.44939 20 10.1509 20 12C20 16.4183 16.4183 20 12 20C10.1509 20 8.44939 19.3736 7.0943 18.3199ZM12 2C6.47715 2 2 6.47715 2 12C2 17.5223 6.47771 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47771 17.5223 2 12 2Z"></path></svg>
                                    <span> Anular</span>
                            </a>
                            </div>
                            </div>';
                return $btn;
            })
            ->rawColumns(['cliente', 'documento', 'estado', 'acciones'])
            ->toJson();
    }

    public function print_ticket(Request $request)
    {
        if (!$request->ajax()) {
            echo json_encode([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
            return;
        }

        return $this->printTicketModern($request);

        $id                 = $request->input('id');
        $data               = [];
        $customPaper        = [0, 0, 226.77, 900.00];

        // Obtener negocio y datos relacionados
        $data["business"]   = Business::find(1);
        $ruc                = '';

        // Obtener nota de venta con sus relaciones
        $factura = SaleNote::with([
            'cliente',
            'pago',
            'usuario'
        ])->findOrFail($id);

        $data["factura"]    = $factura;

        $codigo_comprobante = TypeDocument::where('id', $factura->idtipo_comprobante)->value('codigo');
        $data["name"]       = "{$codigo_comprobante}-{$factura->serie}-{$factura->correlativo}";

        // Obtener detalles de la nota de venta
        $data['detalle'] = DetailSaleNote::select(
            'detail_sale_notes.*',
            'products.descripcion as producto',
            'products.codigo_interno as codigo_interno'
        )
            ->join('products', 'detail_sale_notes.idproducto', '=', 'products.id')
            ->where('idnotaventa', $factura->id)
            ->get();

        // Obtener datos adicionales
        $data['tipo_comprobante']   = TypeDocument::find($factura->idtipo_comprobante);
        $data['tipo_documento']     = $data['tipo_comprobante'];
        $data['vendedor']           = mb_strtoupper($factura->usuario->user ?? '');
        $data['modo_pago']          = $factura->pago->descripcion ?? ''; // Se obtiene desde la relación
        $data['payment_modes']      = DetailPayment::select(
            'detail_payments.*',
            'pay_modes.descripcion as modo_pago'
        )
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where([
                ['idfactura', $factura->id],
                ['idtipo_comprobante', $factura->idtipo_comprobante]
            ])
            ->get();

        $data['count_payment']  = $data['payment_modes']->count();

        // Convertir número a letras
        $formatter = new NumeroALetras();
        $data['numero_letras']  = $formatter->toWords($factura->total, 2);
        $data['signo']          = $this->signo_pais();
        $data["logo"]           = Business::first()->logo;
        $data['moneda']         = $this->moneda_pais();
        $pdf                    = PDF::loadView('admin.pos.ticket_sn', $data)->setPaper($customPaper, 'portrait');
        $pdf->save(public_path('files/sale-notes/ticket/' . $data["name"] . '.pdf'));

        echo json_encode([
            'status'    => true,
            'pdf'       => $data["name"] . '.pdf'
        ]);
    }

    protected function printTicketModern(Request $request)
    {
        $id = (int) $request->input('id');
        $saleNote = $this->saleNotesByCurrentWarehouse()
            ->with(['cliente.tipoDocumento', 'usuario'])
            ->findOrFail($id);
        $typeDocument = TypeDocument::find($saleNote->idtipo_comprobante);
        $warehouse = Warehouse::find((int) Auth::user()->idalmacen);
        $business = $this->resolveBusinessForWarehouse(Business::find(1), $warehouse);
        $name = ($typeDocument?->codigo ?? '02') . '-' . $saleNote->serie . '-' . $saleNote->correlativo;
        $payments = DetailPayment::select('detail_payments.*', 'pay_modes.descripcion as modo_pago')
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where('idfactura', $saleNote->id)
            ->where('idtipo_comprobante', $saleNote->idtipo_comprobante)
            ->get();
        $details = DetailSaleNote::select('detail_sale_notes.*', 'products.descripcion as producto')
            ->join('products', 'detail_sale_notes.idproducto', '=', 'products.id')
            ->where('idnotaventa', $saleNote->id)
            ->get();

        $formatter = new NumeroALetras();
        $data = [
            'name' => $name,
            'business' => $business,
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

        $pdf = PDF::loadView('admin.pos.ticket_document', $data)->setPaper([0, 0, 226.77, 900.00], 'portrait');
        $pdf->save(public_path('files/sale-notes/ticket/' . $name . '.pdf'));

        return response()->json([
            'status' => true,
            'pdf' => $name . '.pdf',
        ]);
    }

    public function print_a4(Request $request)
    {
        if (!$request->ajax()) {
            echo json_encode([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
            return;
        }

        $id                         = $request->input('id');
        $data['factura']            = $this->saleNotesByCurrentWarehouse()->where('id', $id)->first();
        if (!$data['factura']) {
            return response()->json([
                'status'    => false,
                'msg'       => 'La nota de venta no existe en el almacen activo.',
                'type'      => 'warning'
            ], 404);
        }
        $warehouse                  = Warehouse::find((int) Auth::user()->idalmacen);
        $data["business"]           = $this->resolveBusinessForWarehouse(Business::where('id', 1)->first(), $warehouse);
        $data["client"]             = Client::where('id', $data["factura"]["idcliente"])->first();
        $data["name"]               = mb_strtoupper($data["client"]->nro_documento . '-' . $data["factura"]["serie"]) . '-' . $data["factura"]["correlativo"];
        $data["type_document"]      = TypeDocument::where('id', $data["factura"]["idtipo_comprobante"])->first();
        $formatter                  = new NumeroALetras();
        $data['numero_letras']      = $formatter->toWords($data["factura"]->total, 2);
        $data["logo"]       = Business::first()->logo;
        $data['detail']            = DetailSaleNote::select(
            'detail_sale_notes.*',
            'products.descripcion as producto',
            'products.codigo_interno as codigo_interno',
            'units.codigo as unidad'
        )
            ->join('products', 'detail_sale_notes.idproducto', '=', 'products.id')
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->where('idnotaventa', $data["factura"]->id)
            ->get();

        $data["moneda"]             = $this->moneda_pais();
        $data["signo"]              = $this->signo_pais();

        $pdf    = PDF::loadView('admin.sale_notes.pdf', $data)->setPaper('A4', 'portrait');
        $pdf->save(public_path('files/sale-notes/a4/' . $data["name"] . '.pdf'));
        echo json_encode([
            'status'    => true,
            'pdf'       => $data["name"] . '.pdf'
        ]);
    }

    public function anulled(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id             = $request->input('id');
        $sale_note      = $this->saleNotesByCurrentWarehouse()->where('id', $id)->first();
        if (!$sale_note) {
            return response()->json([
                'status'    => false,
                'msg'       => 'La venta no existe en el almacen activo',
                'type'      => 'warning'
            ], 404);
        }
        $detail_sale    = DetailSaleNote::where('idnotaventa', $id)->get();

        if ($sale_note->estado == '2') {
            return response()->json([
                'status'    => false,
                'msg'       => 'La venta se encuentra anulada',
                'type'      => 'warning'
            ]);
        }

        foreach ($detail_sale as $product) {
            $idproducto     = (int) $product["idproducto"];
            $idalmacen      = (int) $product["idalmacen"];
            $cantidad       = intval($product["cantidad"]);
            $product        = Product::where('id', $idproducto)->first();

            if ($product->opcion == 1) {
                $registro  = StockProduct::where('idproducto', $idproducto)->where('idalmacen', $idalmacen)->first();
                StockProduct::where('idproducto', $product["id"])->where('idalmacen', $idalmacen)->update([
                    'stock_actual'  => $registro->stock_actual + $cantidad
                ]);
            }
        }

        DetailPayment::where('idtipo_comprobante', 7)->where('idfactura', $id)->update([
            'estado'    => 2
        ]);

        $this->saleNotesByCurrentWarehouse()->where('id', $id)->update([
            'estado' => 2
        ]);

        return response()->json([
            'status'    => true,
            'msg'       => 'Venta anulada correctamente',
            'type'      => 'success'
        ]);
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
}
