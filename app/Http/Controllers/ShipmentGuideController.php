<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\Department;
use App\Models\District;
use App\Models\Product;
use App\Models\Province;
use App\Models\Serie;
use App\Models\ShipmentGuide;
use App\Models\TypeDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ShipmentGuideController extends Controller
{
    private const TICKET_PAPER_80 = [0, 0, 226.77, 900.00];

    public function index()
    {
        $query = $this->guidesByCurrentWarehouse();

        return view('admin.shipment_guides.list', [
            'kpi_today_count' => (clone $query)->whereDate('fecha_emision', Carbon::today())->count(),
            'kpi_today_pending' => (clone $query)->whereNull('cdr')->count(),
            'kpi_today_customers' => (clone $query)->distinct('idcliente')->count('idcliente'),
        ]);
    }

    public function create()
    {
        $warehouseId = $this->currentWarehouseId();
        $warehouse = $warehouseId > 0 ? \App\Models\Warehouse::query()->find($warehouseId) : null;

        $clients = Client::query()
            ->orderBy('nombres')
            ->get(['id', 'nro_documento', 'nombres', 'direccion', 'ubigeo']);

        $products = Product::query()
            ->orderBy('descripcion')
            ->get(['id', 'codigo_interno', 'descripcion', 'idunidad']);

        return view('admin.shipment_guides.create', [
            'warehouse' => $warehouse,
            'clients' => $clients,
            'products' => $products,
            'transportReasons' => $this->transportReasons(),
            'partidaUbigeoOption' => $this->resolveUbigeoOption(Business::query()->find(1)?->ubigeo),
        ]);
    }

    public function get(Request $request)
    {
        $guides = $this->guidesByCurrentWarehouse()
            ->select([
                'shipment_guides.*',
                'clients.nombres as cliente',
                'clients.nro_documento as cliente_documento',
                'warehouses.descripcion as almacen',
            ])
            ->join('clients', 'clients.id', '=', 'shipment_guides.idcliente')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'shipment_guides.idalmacen')
            ->orderByDesc('shipment_guides.id');

        if ($request->filled('filter_customer')) {
            $guides->where('clients.nombres', 'like', '%' . trim((string) $request->input('filter_customer')) . '%');
        }

        if ($request->filled('filter_document')) {
            $search = trim((string) $request->input('filter_document'));
            $guides->whereRaw("CONCAT(shipment_guides.serie, '-', shipment_guides.correlativo) like ?", ['%' . $search . '%']);
        }

        if ($request->filled('filter_date')) {
            $guides->whereDate('shipment_guides.fecha_emision', $request->input('filter_date'));
        }

        return datatables()
            ->of($guides)
            ->editColumn('fecha_emision', fn ($guide) => optional($guide->fecha_emision)->format('Y-m-d'))
            ->addColumn('guia', fn ($guide) => '<div class="text-center"><div class="fw-semibold">' . e($guide->serie . '-' . $guide->correlativo) . '</div><small class="text-muted">' . e((string) $guide->motivo_traslado_descripcion) . '</small></div>')
            ->addColumn('cliente_info', fn ($guide) => '<div><div class="fw-semibold">' . e((string) $guide->cliente) . '</div><small class="text-muted">' . e((string) ($guide->cliente_documento ?: 'Sin documento')) . '</small></div>')
            ->addColumn('modo_badge', function ($guide) {
                $label = (string) $guide->modo_transporte === '01' ? 'Publico' : 'Privado';
                $class = (string) $guide->modo_transporte === '01' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary';

                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->addColumn('almacen_badge', fn ($guide) => '<span class="badge bg-light text-dark border">' . e((string) ($guide->almacen ?: 'Sin almacen')) . '</span>')
            ->addColumn('xml', fn () => '<span class="text-muted">-</span>')
            ->addColumn('cdr_archivo', fn () => '<span class="text-muted">-</span>')
            ->addColumn('gre_badge', fn ($guide) => $guide->cdr ? '<span class="badge bg-success-subtle text-success">Emitida</span>' : '<span class="badge bg-light text-dark border">Pendiente</span>')
            ->addColumn('acciones', function ($guide) {
                return '<div class="dropdown">
                            <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item btn-detail-guide" data-id="' . (int) $guide->id . '" href="javascript:void(0);">Ver detalle</a>
                                <a class="dropdown-item btn-a4-guide" data-id="' . (int) $guide->id . '" href="javascript:void(0);">A4</a>
                                <a class="dropdown-item btn-ticket-guide" data-id="' . (int) $guide->id . '" href="javascript:void(0);">Ticket</a>
                            </div>
                        </div>';
            })
            ->rawColumns(['guia', 'cliente_info', 'modo_badge', 'almacen_badge', 'xml', 'cdr_archivo', 'gre_badge', 'acciones'])
            ->toJson();
    }

    public function searchUbigeo(Request $request)
    {
        $term = trim((string) $request->input('q'));

        $query = District::query()
            ->select([
                'districts.codigo',
                'districts.descripcion as district_name',
                'provinces.descripcion as province_name',
                'departments.descripcion as department_name',
            ])
            ->join('provinces', function ($join) {
                $join->on('provinces.codigo', '=', 'districts.provincia_codigo')
                    ->on('provinces.departamento_codigo', '=', 'districts.departamento_codigo');
            })
            ->join('departments', 'departments.codigo', '=', 'districts.departamento_codigo');

        if ($term !== '') {
            $query->where(function ($builder) use ($term) {
                $builder->where('districts.codigo', 'like', '%' . $term . '%')
                    ->orWhere('districts.descripcion', 'like', '%' . $term . '%')
                    ->orWhere('provinces.descripcion', 'like', '%' . $term . '%')
                    ->orWhere('departments.descripcion', 'like', '%' . $term . '%');
            });
        }

        $results = $query->orderBy('departments.descripcion')
            ->orderBy('provinces.descripcion')
            ->orderBy('districts.descripcion')
            ->limit(20)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->codigo,
                'text' => $item->department_name . ' / ' . $item->province_name . ' / ' . $item->district_name,
            ])
            ->values();

        return response()->json(['results' => $results]);
    }

    public function save(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo.', 'type' => 'warning'], 422);
        }

        $validator = Validator::make($request->all(), [
            'fecha_emision' => 'required|date',
            'fecha_inicio_traslado' => 'required|date',
            'motivo_traslado_codigo' => 'required|string|max:4',
            'motivo_traslado_descripcion' => 'required|string|max:150',
            'modo_transporte' => 'required|in:01,02',
            'idcliente' => 'required|exists:clients,id',
            'peso_total' => 'required|numeric|min:0.001',
            'unidad_peso' => 'required|string|max:3',
            'partida_direccion' => 'required|string|max:255',
            'llegada_direccion' => 'required|string|max:255',
            'placa_vehiculo' => 'nullable|string|max:20',
            'placa_secundaria' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:255',
            'items.*.cantidad' => 'required|numeric|min:0.01',
        ], [
            'items.required' => 'Debe agregar al menos un item a la guia.',
            'partida_direccion.required' => 'Debe ingresar la direccion de partida.',
            'llegada_direccion.required' => 'Debe ingresar la direccion de llegada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        if ((string) $request->input('modo_transporte') === '02' && trim((string) $request->input('placa_vehiculo')) === '') {
            return response()->json([
                'status' => false,
                'msg' => 'Debe ingresar la placa principal para transporte privado.',
                'type' => 'warning',
            ], 422);
        }

        $typeDocument = TypeDocument::query()->where('codigo', '09')->first();
        $warehouseId = $this->currentWarehouseId();
        $serieData = $this->resolveGuideSerie($warehouseId, $typeDocument?->id);

        $guide = DB::transaction(function () use ($request, $warehouseId, $typeDocument, $serieData) {
            $guide = ShipmentGuide::query()->create([
                'idcliente' => (int) $request->input('idcliente'),
                'idalmacen' => $warehouseId > 0 ? $warehouseId : null,
                'idtipo_comprobante' => $typeDocument?->id,
                'serie' => $serieData['serie'],
                'correlativo' => $serieData['correlativo'],
                'fecha_emision' => $request->input('fecha_emision'),
                'fecha_inicio_traslado' => $request->input('fecha_inicio_traslado'),
                'motivo_traslado_codigo' => $request->input('motivo_traslado_codigo'),
                'motivo_traslado_descripcion' => $request->input('motivo_traslado_descripcion'),
                'modo_transporte' => $request->input('modo_transporte'),
                'peso_total' => $request->input('peso_total'),
                'unidad_peso' => strtoupper((string) $request->input('unidad_peso', 'KGM')),
                'partida_ubigeo' => $request->input('partida_ubigeo'),
                'partida_direccion' => mb_strtoupper(trim((string) $request->input('partida_direccion'))),
                'llegada_ubigeo' => $request->input('llegada_ubigeo'),
                'llegada_direccion' => mb_strtoupper(trim((string) $request->input('llegada_direccion'))),
                'transportista_documento_tipo' => $request->input('transportista_documento_tipo'),
                'transportista_documento' => $request->input('transportista_documento'),
                'transportista_nombre' => mb_strtoupper(trim((string) $request->input('transportista_nombre'))),
                'conductor_documento_tipo' => $request->input('conductor_documento_tipo'),
                'conductor_documento' => $request->input('conductor_documento'),
                'conductor_nombre' => mb_strtoupper(trim((string) $request->input('conductor_nombre'))),
                'placa_vehiculo' => strtoupper(trim((string) $request->input('placa_vehiculo'))),
                'placa_secundaria' => strtoupper(trim((string) $request->input('placa_secundaria'))),
                'observaciones' => trim((string) $request->input('observaciones')),
            ]);

            foreach ((array) $request->input('items', []) as $item) {
                $guide->items()->create([
                    'product_id' => filled($item['product_id'] ?? null) ? (int) $item['product_id'] : null,
                    'codigo' => $item['codigo'] ?? null,
                    'descripcion' => mb_strtoupper(trim((string) ($item['descripcion'] ?? ''))),
                    'unidad' => strtoupper((string) ($item['unidad'] ?? 'NIU')),
                    'cantidad' => $item['cantidad'],
                ]);
            }

            if ($serieData['serie_model']) {
                $serieData['serie_model']->update(['correlativo' => $serieData['next_correlativo']]);
            }

            return $guide;
        });

        return response()->json([
            'status' => true,
            'msg' => 'Guia de remision registrada correctamente.',
            'type' => 'success',
            'redirect' => route('admin.shipment_guides'),
            'id' => $guide->id,
        ]);
    }

    public function detail(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo.', 'type' => 'warning'], 422);
        }

        $guide = $this->findAccessibleGuide((int) $request->input('id'));

        if (! $guide) {
            return response()->json(['status' => false, 'msg' => 'La guia no existe.', 'type' => 'warning'], 404);
        }

        $guide->loadMissing(['client', 'warehouse', 'items']);

        return response()->json([
            'status' => true,
            'data' => [
                'serie' => $guide->serie,
                'correlativo' => $guide->correlativo,
                'fecha_emision' => optional($guide->fecha_emision)->format('Y-m-d'),
                'fecha_inicio_traslado' => optional($guide->fecha_inicio_traslado)->format('Y-m-d'),
                'motivo' => $guide->motivo_traslado_descripcion,
                'modo_transporte' => (string) $guide->modo_transporte === '01' ? 'Publico' : 'Privado',
                'cliente' => $guide->client?->nombres,
                'cliente_documento' => $guide->client?->nro_documento,
                'almacen' => $guide->warehouse?->descripcion,
                'placa_vehiculo' => $guide->placa_vehiculo,
                'placa_secundaria' => $guide->placa_secundaria,
                'conductor_nombre' => $guide->conductor_nombre,
                'transportista_nombre' => $guide->transportista_nombre,
                'partida' => $guide->partida_direccion,
                'llegada' => $guide->llegada_direccion,
                'peso_total' => number_format((float) $guide->peso_total, 3, '.', ''),
                'items' => $guide->items->map(fn ($item) => [
                    'descripcion' => $item->descripcion,
                    'codigo' => $item->codigo,
                    'unidad' => $item->unidad,
                    'cantidad' => number_format((float) $item->cantidad, 2, '.', ''),
                ])->values(),
            ],
        ]);
    }

    public function print_ticket(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo.', 'type' => 'warning'], 422);
        }

        $guide = $this->findAccessibleGuide((int) $request->input('id'));

        if (! $guide) {
            return response()->json(['status' => false, 'msg' => 'La guia no existe.', 'type' => 'warning'], 404);
        }

        $path = $this->buildGuidePdf($guide, 'admin.shipment_guides.ticket', self::TICKET_PAPER_80, public_path('files/shipment_guides/ticket'));

        return response()->json(['status' => true, 'pdf' => basename($path)]);
    }

    public function print_a4(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo.', 'type' => 'warning'], 422);
        }

        $guide = $this->findAccessibleGuide((int) $request->input('id'));

        if (! $guide) {
            return response()->json(['status' => false, 'msg' => 'La guia no existe.', 'type' => 'warning'], 404);
        }

        $path = $this->buildGuidePdf($guide, 'admin.shipment_guides.pdf', 'a4', public_path('files/shipment_guides/a4'));

        return response()->json(['status' => true, 'pdf' => basename($path)]);
    }

    protected function guidesByCurrentWarehouse()
    {
        $warehouseId = $this->currentWarehouseId();
        $query = ShipmentGuide::query();

        if ($warehouseId <= 0) {
            return $query;
        }

        return $query->where('shipment_guides.idalmacen', $warehouseId);
    }

    protected function findAccessibleGuide(int $id): ?ShipmentGuide
    {
        return $this->guidesByCurrentWarehouse()->find($id);
    }

    protected function transportReasons(): array
    {
        return [
            ['codigo' => '01', 'descripcion' => 'VENTA'],
            ['codigo' => '02', 'descripcion' => 'COMPRA'],
            ['codigo' => '04', 'descripcion' => 'TRASLADO ENTRE ESTABLECIMIENTOS'],
            ['codigo' => '08', 'descripcion' => 'IMPORTACION'],
            ['codigo' => '09', 'descripcion' => 'EXPORTACION'],
        ];
    }

    protected function resolveGuideSerie(int $warehouseId, ?int $typeDocumentId): array
    {
        $serieModel = null;

        if ($typeDocumentId) {
            $serieQuery = Serie::query()->where('idtipo_documento', $typeDocumentId)->where('estado', 1);

            if ($warehouseId > 0) {
                $serieQuery->whereExists(function ($query) use ($warehouseId) {
                    $query->selectRaw('1')
                        ->from('cashes')
                        ->whereColumn('cashes.id', 'series.idcaja')
                        ->where('cashes.idalmacen', $warehouseId);
                });
            }

            $serieModel = $serieQuery->orderBy('id')->first();
        }

        $serie = $serieModel?->serie ?: 'T001';
        $nextCorrelativo = (int) ($serieModel?->correlativo ?: 1);

        if (! $serieModel) {
            $lastCorrelativo = (int) ShipmentGuide::query()->where('serie', $serie)->max(DB::raw('CAST(correlativo as unsigned)'));
            $nextCorrelativo = $lastCorrelativo + 1;
        }

        return [
            'serie' => $serie,
            'correlativo' => str_pad((string) $nextCorrelativo, 8, '0', STR_PAD_LEFT),
            'next_correlativo' => $nextCorrelativo + 1,
            'serie_model' => $serieModel,
        ];
    }

    protected function resolveUbigeoOption(?string $ubigeo): ?array
    {
        if (! $ubigeo) {
            return null;
        }

        $district = District::query()->where('codigo', $ubigeo)->first();

        if (! $district) {
            return null;
        }

        $province = Province::query()
            ->where('codigo', $district->provincia_codigo)
            ->where('departamento_codigo', $district->departamento_codigo)
            ->first();
        $department = Department::query()->where('codigo', $district->departamento_codigo)->first();

        return [
            'id' => $district->codigo,
            'text' => trim(($department?->descripcion ?: '') . ' / ' . ($province?->descripcion ?: '') . ' / ' . $district->descripcion),
        ];
    }

    protected function buildGuidePdf(ShipmentGuide $guide, string $view, array|string $paper, string $directory): string
    {
        $guide->loadMissing(['client', 'warehouse', 'items']);
        $business = Business::query()->find(1);
        $pdf = Pdf::loadView($view, compact('guide', 'business'))->setPaper($paper);

        File::ensureDirectoryExists($directory);
        $filename = $guide->serie . '-' . $guide->correlativo . '.pdf';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $pdf->output());

        return $path;
    }
}
