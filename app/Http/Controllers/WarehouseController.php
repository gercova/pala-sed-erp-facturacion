<?php

namespace App\Http\Controllers;

use App\Exports\ProductsWarehouseExport;
use App\Imports\ProductsWarehouseImport;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('admin.warehouses.list');
    }

    public function get()
    {
        $warehouses = Warehouse::query()
            ->select('warehouses.*')
            ->selectSub(function ($query) {
                $query->from('stock_products')
                    ->selectRaw('COALESCE(SUM(stock_actual), 0)')
                    ->whereColumn('stock_products.idalmacen', 'warehouses.id');
            }, 'unidades')
            ->orderByDesc('id');

        return datatables()
            ->of($warehouses)
            ->addColumn('pin_column', function () {
                return '<img src="' . asset('assets/img/pin_home.png') . '" class="img-fluid" alt="" width="80%">';
            })
            ->addColumn('descripcion_completa', function (Warehouse $warehouse) {
                return '<div>
                            <strong>' . e($warehouse->descripcion) . '</strong><br>
                            <span class="text-muted" style="font-size: 12px;">' . e($warehouse->direccion ?: 'Sin dirección registrada') . '</span>
                        </div>';
            })
            ->addColumn('acciones', function (Warehouse $warehouse) {
                $id = $warehouse->id;
                return '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z" class="menu-icon"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                <a class="dropdown-item" data-id="' . $id . '" href="' . route('admin.products_warehouse', $id) . '">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon menu-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                                <line x1="7" x2="7.01" y1="7" y2="7" />
                                </svg>
                                <span> Productos</span>
                            </a>
                            <a class="dropdown-item btn-detail" data-id="' . $id . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit menu-icon"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span> Actualizar</span>
                                </a>
                                    <a class="dropdown-item btn-confirm" data-id="' . $id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 menu-icon"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    <span> Eliminar</span>
                            </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['pin_column', 'descripcion_completa', 'acciones'])
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

        $validator = $this->validateWarehouseRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        if ($this->warehouseDescriptionExists($data['descripcion'])) {
            return response()->json([
                'status' => false,
                'msg' => 'Ya existe un almacén con esa descripción.',
                'type' => 'warning',
            ], 422);
        }

        Warehouse::create([
            'descripcion' => mb_strtoupper(trim((string) $data['descripcion'])),
            'direccion' => mb_strtoupper(trim((string) $data['direccion'])),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos agregados correctamente',
            'type' => 'success',
        ]);
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

        $warehouse = Warehouse::query()->find((int) $request->input('id'));
        if (! $warehouse) {
            return response()->json([
                'status' => false,
                'msg' => 'El almacén no existe.',
                'type' => 'warning',
            ], 404);
        }

        return response()->json(['status' => true, 'warehouse' => $warehouse]);
    }

    public function store(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $warehouse = Warehouse::query()->find((int) $request->input('id'));
        if (! $warehouse) {
            return response()->json([
                'status' => false,
                'msg' => 'El almacén no existe.',
                'type' => 'warning',
            ], 404);
        }

        $validator = $this->validateWarehouseRequest($request, true);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        if ($this->warehouseDescriptionExists($data['descripcion'], $warehouse->id)) {
            return response()->json([
                'status' => false,
                'msg' => 'Ya existe otro almacén con esa descripción.',
                'type' => 'warning',
            ], 422);
        }

        $warehouse->update([
            'descripcion' => mb_strtoupper(trim((string) $data['descripcion'])),
            'direccion' => mb_strtoupper(trim((string) $data['direccion'])),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success',
        ]);
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

        $warehouse = Warehouse::query()->find((int) $request->input('id'));
        if (! $warehouse) {
            return response()->json([
                'status' => false,
                'msg' => 'El almacén no existe.',
                'type' => 'warning',
            ], 404);
        }

        $hasProducts = StockProduct::query()->where('idalmacen', $warehouse->id)->exists();
        if ($hasProducts) {
            return response()->json([
                'status' => false,
                'msg' => 'El establecimiento tiene productos registrados',
                'type' => 'warning',
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
            'type' => 'success',
        ]);
    }

    public function products($id)
    {
        $data['warehouse'] = Warehouse::query()->findOrFail((int) $id);
        return view('admin.warehouses.products.list', $data);
    }

    public function get_prod_warehouse(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $id = $request->input('id');
        $query = StockProduct::select(
            'products.codigo_barras',
            'products.descripcion as producto',
            'products.codigo_interno',
            'products.opcion',
            'stock_products.stock_minimo',
            'stock_products.stock_actual',
            'stock_products.idproducto',
            'stock_products.idalmacen',
            'stock_products.precio_compra',
            'stock_products.precio_venta'
        )
            ->join('products', 'stock_products.idproducto', 'products.id')
            ->where('stock_products.idalmacen', $id);

        if ($request->has('barcode') && $request->barcode != '') {
            $query->where('products.codigo_barras', 'like', '%' . $request->barcode . '%');
        }
        if ($request->has('code_intern') && $request->code_intern != '') {
            $query->where('products.codigo_interno', 'like', '%' . $request->code_intern . '%');
        }
        if ($request->has('description') && $request->description != '') {
            $query->where('products.descripcion', 'like', '%' . $request->description . '%');
        }
        if ($request->has('price_buy') && $request->price_buy != '') {
            $query->where('stock_products.precio_compra', 'like', '%' . $request->price_buy . '%');
        }
        if ($request->has('price_sale') && $request->price_sale != '') {
            $query->where('stock_products.precio_venta', 'like', '%' . $request->price_sale . '%');
        }
        if ($request->has('stock_min') && $request->stock_min != '') {
            $query->where('stock_products.stock_minimo', 'like', '%' . $request->stock_min . '%');
        }
        if ($request->has('stock_act') && $request->stock_act != '') {
            $query->where('stock_products.stock_actual', 'like', '%' . $request->stock_act . '%');
        }

        $productos = $query->orderByDesc('stock_products.created_at')->get();
        return datatables()
            ->of($productos)
            ->editColumn('producto', function ($producto) {
                $typeBadge = (int) $producto->opcion === 2
                    ? '<span class="badge bg-info-subtle text-info fw-medium ms-2">Servicio</span>'
                    : '<span class="badge bg-success-subtle text-success fw-medium ms-2">Producto</span>';

                return '<div><div class="fw-semibold">' . e($producto->producto) . '</div><div class="small text-muted mt-1">' . $typeBadge . '</div></div>';
            })
            ->addColumn('precio_compra', function ($producto) {
                return '<input type="text" class="align-middle form-control form-control-sm text-center input-precio-compra" 
                            name="input-precio-compra" value="' . number_format((float) $producto->precio_compra, 2, '.', '') . '" 
                            data-idalmacen="' . $producto->idalmacen . '" 
                            data-idproducto="' . $producto->idproducto . '" 
                            data-stock_minimo="' . $producto->stock_minimo . '" 
                            data-precio_venta="' . $producto->precio_venta . '"
                            >';
            })
            ->addColumn('precio_venta', function ($producto) {
                return '<input type="text" class="align-middle form-control form-control-sm text-center input-precio-venta" 
                            name="input-precio-venta" value="' . number_format((float) $producto->precio_venta, 2, '.', '') . '" 
                            data-idalmacen="' . $producto->idalmacen . '" 
                            data-idproducto="' . $producto->idproducto . '" 
                            data-stock_minimo="' . $producto->stock_minimo . '" 
                            data-precio_compra="' . $producto->precio_compra . '">';
            })
            ->addColumn('stock_minimo', function ($producto) {
                if ($producto->opcion == 1) {
                    return '<input type="text" class="align-middle form-control form-control-sm text-center input-stock-minimo" 
                        name="input-stock-minimo" value="' . (int) $producto->stock_minimo . '" 
                        data-idalmacen="' . $producto->idalmacen . '" 
                        data-idproducto="' . $producto->idproducto . '" 
                        data-precio_compra="' . $producto->precio_compra . '" 
                        data-precio_venta="' . $producto->precio_venta . '">';
                }
                return '<span class="badge bg-light text-muted border">No aplica</span>';
            })
            ->editColumn('stock_actual', function ($producto) {
                if ((int) $producto->opcion === 2) {
                    return '<span class="badge bg-light text-muted border">No inventariable</span>';
                }

                $stockActual = (int) $producto->stock_actual;
                $stockMinimo = (int) $producto->stock_minimo;
                $badgeClass = 'bg-success-subtle text-success';
                $label = 'En stock';

                if ($stockActual <= 0) {
                    $badgeClass = 'bg-danger-subtle text-danger';
                    $label = 'Sin stock';
                } elseif ($stockMinimo > 0 && $stockActual <= $stockMinimo) {
                    $badgeClass = 'bg-warning-subtle text-warning';
                    $label = 'Stock bajo';
                }

                return '<div class="text-center"><div class="fw-semibold">' . $stockActual . '</div><div class="mt-1"><span class="badge ' . $badgeClass . '">' . $label . '</span></div></div>';
            })
            ->addColumn('acciones', function ($producto) {
                $hidden = ($producto->opcion == 1) ? '' : 'd-none';
                return '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path>
                                </svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                <a class="dropdown-item btn-detail-sum ' . $hidden . '" data-idalmacen="' . $producto->idalmacen . '" data-idproducto="' . $producto->idproducto . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus mr-50 menu-icon">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span> Sumar</span>
                            </a>
                            <a class="dropdown-item btn-detail-stock ' . $hidden . '" data-idalmacen="' . $producto->idalmacen . '" data-idproducto="' . $producto->idproducto . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 mr-50 menu-icon">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                <span> Actualizar</span>
                            </a>
                            <a class="dropdown-item btn-confirm-stock" data-idalmacen="' . $producto->idalmacen . '" data-idproducto="' . $producto->idproducto . '" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash mr-50 menu-icon">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                <span> Eliminar</span>
                            </a>
                            </div>
                        </div>';
            })
            ->rawColumns(['producto', 'precio_compra', 'precio_venta', 'stock_minimo', 'stock_actual', 'acciones'])
            ->toJson();
    }

    public function get_detail_products(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = $request->input('id');
        $ids_productos_stocks = StockProduct::where('idalmacen', $id)->pluck('idproducto')->toArray();

        $productos = Product::select('products.*', 'categories.descripcion as categoria')
            ->join('categories', 'products.idcategoria', 'categories.id');

        if ($request->has('search_barcode') && $request->search_barcode) {
            $productos->where('products.codigo_barras', 'LIKE', '%' . $request->search_barcode . '%');
        }

        if ($request->has('search_code_intern') && $request->search_code_intern) {
            $productos->where('products.codigo_interno', 'LIKE', '%' . $request->search_code_intern . '%');
        }

        if ($request->has('search_description') && $request->search_description) {
            $productos->where('products.descripcion', 'LIKE', '%' . $request->search_description . '%');
        }

        if ($request->has('search_category') && $request->search_category) {
            $productos->whereHas('category', function ($query) use ($request) {
                $query->where('categories.descripcion', 'LIKE', '%' . $request->search_category . '%');
            });
        }

        if (! empty($ids_productos_stocks)) {
            $productos->whereNotIn('products.id', $ids_productos_stocks);
        }

        $productos = $productos->orderByDesc('products.id')->get();

        return datatables()
            ->of($productos)
            ->addColumn('checkbox', function ($producto) {
                return '<input type="checkbox" class="align-middle checkbox-item btn-checkbox" data-precio_compra="' . $producto->precio_compra . '" data-id="' . $producto->id . '" data-precio_venta="' . $producto->precio_venta . '">';
            })
            ->addColumn('stock_inicial', function ($producto) {
                $tipo = ($producto->opcion == 1) ? 'producto' : 'servicio';
                $hidden = ($producto->opcion == 1) ? 'text' : 'hidden';
                return '<input type="' . $hidden . '" class="align-middle form-control form-control-sm" 
                name="input-stock" data-id="' . $producto->id . '" disabled 
                data-tipo="' . $tipo . '" data-precio_compra="' . $producto->precio_compra . '" 
                data-precio_venta="' . $producto->precio_venta . '" 
                onkeydown="return event.key !== \'Enter\';">';
            })
            ->rawColumns(['checkbox', 'stock_inicial'])
            ->toJson();
    }

    public function list_products(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        return response()->json([
            'status' => true,
            'id' => $request->input('id'),
        ]);
    }

    public function save_product_stock(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $warehouse = $this->findWarehouseOrFail($idalmacen);
        if ($warehouse !== null) {
            return $warehouse;
        }

        $productos = json_decode((string) $request->post('productos'));
        $fecha_registro = date('Y-m-d');

        if (! is_array($productos) || empty($productos)) {
            return response()->json([
                'status' => false,
                'msg' => 'No tiene productos para agregar',
                'type' => 'warning',
            ]);
        }

        foreach ($productos as $producto) {
            $product = Product::query()->find((int) ($producto->idproducto ?? 0));
            if (! $product) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Uno de los productos seleccionados ya no existe.',
                    'type' => 'warning',
                ], 404);
            }

            $isService = (int) $product->opcion === 2;
            $stockInicial = $isService ? null : (int) ($producto->stock_inicial ?? 0);
            if (! $isService && $stockInicial < 0) {
                return response()->json([
                    'status' => false,
                    'msg' => 'El stock inicial no puede ser negativo.',
                    'type' => 'warning',
                ], 422);
            }

            StockProduct::insert([
                'idalmacen' => $idalmacen,
                'stock_minimo' => $isService ? null : 5,
                'idproducto' => $product->id,
                'stock_actual' => $stockInicial,
                'precio_compra' => number_format((float) ($producto->precio_compra ?? 0), 2, '.', ''),
                'precio_venta' => number_format((float) ($producto->precio_venta ?? 0), 2, '.', ''),
                'fecha_registro' => $fecha_registro,
                'stock_entrada' => $stockInicial,
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Datos guardados correctamente',
            'type' => 'success',
        ]);
    }

    public function barcode_sum(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $warehouse = $this->findWarehouseOrFail($idalmacen);
        if ($warehouse !== null) {
            return $warehouse;
        }

        $barcode = trim((string) $request->input('barcode'));
        $producto = Product::where('codigo_barras', $barcode)->first();
        $cantidad = 1;
        if (empty($producto)) {
            return response()->json([
                'status' => false,
                'msg' => 'Producto no registrado',
                'type' => 'warning',
            ]);
        }

        $buscar_producto = StockProduct::where('idalmacen', $idalmacen)->where('idproducto', $producto->id)->first();

        if (empty($buscar_producto)) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no se encuentra en almacén',
                'type' => 'warning',
            ]);
        }

        if ((int) $producto->opcion === 2) {
            return response()->json([
                'status' => false,
                'msg' => 'Los servicios no manejan stock fÃ­sico por almacÃ©n.',
                'type' => 'warning',
            ], 422);
        }

        $stock_db = StockProduct::where('idalmacen', $idalmacen)->where('idproducto', $producto->id)->first();
        StockProduct::where('idalmacen', $idalmacen)->where('idproducto', $producto->id)->update([
            'stock_actual' => $stock_db->stock_actual + $cantidad,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Stock actualizado correctamente',
            'type' => 'success',
        ]);
    }

    public function save_products_all(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $warehouse = $this->findWarehouseOrFail($idalmacen);
        if ($warehouse !== null) {
            return $warehouse;
        }

        $stock_inicial = (int) $request->input('stock_inicial');
        $fecha_registro = date('Y-m-d');

        $ids_productos_db = Product::pluck('id')->toArray();
        $ids_productos_stocks = StockProduct::where('idalmacen', $idalmacen)->pluck('idproducto')->toArray();
        $productos = [];
        $nuevo_array = array_diff($ids_productos_db, $ids_productos_stocks);

        foreach ($nuevo_array as $key) {
            $productos[] = Product::where('id', $key)->first();
        }

        if (empty($productos)) {
            return response()->json([
                'status' => false,
                'msg' => 'No hay productos para añadir al establecimiento.',
                'type' => 'warning',
            ]);
        }

        $hay_productos = false;

        foreach ($productos as $producto) {
            if ($producto->opcion == 1) {
                $hay_productos = true;
                break;
            }
        }

        if ($hay_productos && $stock_inicial <= 0) {
            return response()->json([
                'status' => false,
                'msg' => 'El stock inicial debe ser mayor a cero para un producto.',
                'type' => 'warning',
            ]);
        }

        foreach ($productos as $producto) {
            StockProduct::insert([
                'idalmacen' => $idalmacen,
                'stock_minimo' => 5,
                'idproducto' => $producto->id,
                'stock_actual' => ($producto->opcion == 1) ? $stock_inicial : null,
                'precio_compra' => $producto->precio_compra,
                'precio_venta' => $producto->precio_venta,
                'fecha_registro' => $fecha_registro,
                'stock_entrada' => ($producto->opcion == 1) ? $stock_inicial : null,
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Datos guardados correctamente',
            'type' => 'success',
        ]);
    }

    public function detail_sum(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $stock = $this->findStockProduct((int) $request->input('idalmacen'), (int) $request->input('idproducto'));
        if ($stock instanceof \Illuminate\Http\JsonResponse) {
            return $stock;
        }

        if ((int) $stock->opcion === 2) {
            return response()->json([
                'status' => false,
                'msg' => 'Los servicios no manejan stock fÃ­sico.',
                'type' => 'warning',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'stock' => $stock,
        ]);
    }

    public function sum_stock(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $idproducto = (int) $request->input('idproducto');
        $cantidad = (int) $request->input('cantidad');
        if ($cantidad <= 0) {
            return response()->json([
                'status' => false,
                'msg' => 'La cantidad a sumar debe ser mayor a cero.',
                'type' => 'warning',
            ], 422);
        }

        $stock_db = $this->findStockProduct($idalmacen, $idproducto);
        if ($stock_db instanceof \Illuminate\Http\JsonResponse) {
            return $stock_db;
        }

        if ((int) $stock_db->opcion === 2) {
            return response()->json([
                'status' => false,
                'msg' => 'Los servicios no manejan stock fÃ­sico.',
                'type' => 'warning',
            ], 422);
        }

        StockProduct::where('idalmacen', $idalmacen)->where('idproducto', $idproducto)->update([
            'stock_actual' => $stock_db->stock_actual + $cantidad,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success',
        ]);
    }

    public function detail_stock(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $stock = $this->findStockProduct((int) $request->input('idalmacen'), (int) $request->input('idproducto'));
        if ($stock instanceof \Illuminate\Http\JsonResponse) {
            return $stock;
        }

        if ((int) $stock->opcion === 2) {
            return response()->json([
                'status' => false,
                'msg' => 'Los servicios no manejan stock fÃ­sico.',
                'type' => 'warning',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'stock' => $stock,
        ]);
    }

    public function store_stock(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $idproducto = (int) $request->input('idproducto');
        $stock_actual = (int) $request->input('stock_actual');
        if ($stock_actual < 0) {
            return response()->json([
                'status' => false,
                'msg' => 'El stock actual no puede ser negativo.',
                'type' => 'warning',
            ], 422);
        }

        $stock = $this->findStockProduct($idalmacen, $idproducto);
        if ($stock instanceof \Illuminate\Http\JsonResponse) {
            return $stock;
        }

        if ((int) $stock->opcion === 2) {
            return response()->json([
                'status' => false,
                'msg' => 'Los servicios no manejan stock fÃ­sico.',
                'type' => 'warning',
            ], 422);
        }

        StockProduct::where('idalmacen', $idalmacen)->where('idproducto', $idproducto)->update([
            'stock_actual' => $stock_actual,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success',
        ]);
    }

    public function delete_stock(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $stock = $this->findStockProduct((int) $request->input('idalmacen'), (int) $request->input('idproducto'));
        if ($stock instanceof \Illuminate\Http\JsonResponse) {
            return $stock;
        }

        if ((int) $stock->opcion === 1 && (int) $stock->stock_actual > 0) {
            return response()->json([
                'status' => false,
                'msg' => 'No se puede retirar el producto mientras tenga stock en el almacÃ©n.',
                'type' => 'warning',
            ], 422);
        }

        StockProduct::where('idalmacen', (int) $request->input('idalmacen'))
            ->where('idproducto', (int) $request->input('idproducto'))
            ->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
            'type' => 'success',
        ]);
    }

    public function store_product_stocks(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = (int) $request->input('idalmacen');
        $idproducto = (int) $request->input('idproducto');
        $stock = $this->findStockProduct($idalmacen, $idproducto);
        if ($stock instanceof \Illuminate\Http\JsonResponse) {
            return $stock;
        }

        $validator = Validator::make($request->all(), [
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => ((int) $stock->opcion === 1) ? 'nullable|numeric|min:0' : 'nullable',
        ], [
            'precio_compra.required' => 'Debe ingresar el precio de compra.',
            'precio_compra.numeric' => 'El precio de compra debe ser numÃ©rico.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',
            'precio_venta.required' => 'Debe ingresar el precio de venta.',
            'precio_venta.numeric' => 'El precio de venta debe ser numÃ©rico.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'stock_minimo.numeric' => 'El stock mÃ­nimo debe ser numÃ©rico.',
            'stock_minimo.min' => 'El stock mÃ­nimo no puede ser negativo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $stock_minimo = ((int) $stock->opcion === 1) ? (int) ($request->input('stock_minimo') ?: 0) : null;
        $precio_compra = number_format((float) $request->input('precio_compra'), 2, '.', '');
        $precio_venta = number_format((float) $request->input('precio_venta'), 2, '.', '');

        StockProduct::where('idalmacen', $idalmacen)
            ->where('idproducto', $idproducto)
            ->update([
                'stock_minimo' => $stock_minimo,
                'precio_compra' => $precio_compra,
                'precio_venta' => $precio_venta,
            ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success',
        ]);
    }

    public function export_products($id)
    {
        $stock_products = StockProduct::select('products.descripcion', 'products.opcion', 'stock_products.precio_compra', 'stock_products.precio_venta', 'stock_products.stock_minimo', 'stock_products.stock_actual')
            ->join('products', 'stock_products.idproducto', '=', 'products.id')
            ->where('stock_products.idalmacen', $id)
            ->orderByDesc('stock_products.created_at')
            ->get();

        return Excel::download(new ProductsWarehouseExport($stock_products), 'productos_almacen_' . $id . '.xlsx');
    }

    public function upload_excel_products(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $idalmacen = $request->input('idalmacen');
        $excel = $request->file('excelFile');
        if (empty($excel)) {
            return response()->json([
                'status' => false,
                'msg' => 'Seleccione un documento',
                'type' => 'warning',
            ]);
        }

        $extension = $excel->extension();
        if ($extension != 'xlsx') {
            return response()->json([
                'status' => false,
                'msg' => 'Seleccione un documento válido',
                'title' => 'Espere',
                'type' => 'warning',
            ]);
        }

        try {
            Excel::import(new ProductsWarehouseImport($idalmacen), $excel);
            return response()->json([
                'status' => true,
                'msg' => 'Los datos se actualizaron correctamente',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Se encontraron observaciones en el documento ' . $e->getMessage(),
                'type' => 'warning',
            ]);
        }
    }

    private function validateWarehouseRequest(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'descripcion' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
        ];

        if ($isUpdate) {
            $rules['id'] = 'required|integer|exists:warehouses,id';
        }

        return Validator::make($request->all(), $rules, [
            'descripcion.required' => 'Debe ingresar la descripción.',
            'direccion.required' => 'Debe ingresar la dirección.',
        ]);
    }

    private function warehouseDescriptionExists(string $description, ?int $ignoreId = null): bool
    {
        $query = Warehouse::query()
            ->whereRaw('UPPER(descripcion) = ?', [mb_strtoupper(trim($description))]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function findWarehouseOrFail(int $warehouseId): ?\Illuminate\Http\JsonResponse
    {
        if (Warehouse::query()->where('id', $warehouseId)->exists()) {
            return null;
        }

        return response()->json([
            'status' => false,
            'msg' => 'El almacÃ©n no existe.',
            'type' => 'warning',
        ], 404);
    }

    private function findStockProduct(int $warehouseId, int $productId)
    {
        $stock = StockProduct::query()
            ->select('stock_products.*', 'products.opcion')
            ->join('products', 'stock_products.idproducto', '=', 'products.id')
            ->where('stock_products.idalmacen', $warehouseId)
            ->where('stock_products.idproducto', $productId)
            ->first();

        if ($stock) {
            return $stock;
        }

        return response()->json([
            'status' => false,
            'msg' => 'El producto no estÃ¡ registrado en este almacÃ©n.',
            'type' => 'warning',
        ], 404);
    }
}
