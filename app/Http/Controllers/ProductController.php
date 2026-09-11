<?php

namespace App\Http\Controllers;

use App\Exports\ProductsCatalogExport;
use App\Imports\ProductsCatalogImport;
use App\Models\Category;
use App\Models\DetailBilling;
use App\Models\DetailBuy;
use App\Models\DetailQuote;
use App\Models\DetailSaleNote;
use App\Models\DetailTransferOrder;
use App\Models\IgvTypeAffection;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\Unit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        $data['categories'] = Category::query()->orderBy('descripcion')->get();
        $data['units'] = Unit::query()->where('estado', 1)->orderBy('descripcion')->get();
        $data['igvTypeAffections'] = IgvTypeAffection::query()->where('estado', 1)->orderBy('codigo')->get();
        $data['signo'] = $this->signo_pais();
        $data['kpi_total_products'] = Product::count();
        $data['kpi_low_stock'] = StockProduct::where('stock_actual', '<=', 5)->count();
        $data['kpi_updated_today'] = Product::whereDate('updated_at', Carbon::today())->count();

        return view('admin.products.list', $data);
    }

    public function get()
    {
        $products = Product::query()
            ->select('products.*', 'units.codigo as unidad')
            ->join('units', 'products.idunidad', '=', 'units.id')
            ->orderByDesc('products.id');

        return datatables()
            ->of($products)
            ->editColumn('descripcion', function (Product $product) {
                $badges = [];

                if ((int) $product->opcion === 2) {
                    $badges[] = '<span class="badge bg-info-subtle text-info fw-medium ms-2">Servicio</span>';
                } else {
                    $badges[] = '<span class="badge bg-success-subtle text-success fw-medium ms-2">Producto</span>';
                }

                if (! empty($product->codigo_interno)) {
                    $badges[] = '<span class="badge bg-light text-muted border ms-2">Cod: ' . e($product->codigo_interno) . '</span>';
                }

                return '<div><div class="fw-semibold">' . e($product->descripcion) . '</div><div class="small text-muted mt-1">' . implode('', $badges) . '</div></div>';
            })
            ->editColumn('precio_compra', function (Product $product) {
                return number_format((float) $product->precio_compra, 2, '.', '');
            })
            ->editColumn('precio_venta', function (Product $product) {
                return number_format((float) $product->precio_venta, 2, '.', '');
            })
            ->addColumn('acciones', function (Product $product) {
                $id = $product->id;

                return '<div class="dropdown">
                        <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                        <a class="dropdown-item btn-view" data-id="' . $id . '" href="javascript:void(0);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M18.031 16.6168L22.3137 20.8995L20.8995 22.3137L16.6168 18.031C15.0769 19.263 13.124 20 11 20C6.032 20 2 15.968 2 11C2 6.032 6.032 2 11 2C15.968 2 20 6.032 20 11C20 13.124 19.263 15.0769 18.031 16.6168ZM16.0247 15.8748C17.2475 14.6146 18 12.8956 18 11C18 7.1325 14.8675 4 11 4C7.1325 4 4 7.1325 4 11C4 14.8675 7.1325 18 11 18C12.8956 18 14.6146 17.2475 15.8748 16.0247L16.0247 15.8748ZM12.1779 7.17624C11.4834 7.48982 11 8.18846 11 9C11 10.1046 11.8954 11 13 11C13.8115 11 14.5102 10.5166 14.8238 9.82212C14.9383 10.1945 15 10.59 15 11C15 13.2091 13.2091 15 11 15C8.79086 15 7 13.2091 7 11C7 8.79086 8.79086 7 11 7C11.41 7 11.8055 7.06167 12.1779 7.17624Z" ></path></svg>
                                        <span>Ver detalle</span>
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
            ->rawColumns(['descripcion', 'acciones'])
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

        $validator = $this->validateProductRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $duplicateMessage = $this->getDuplicateMessage($data);
        if ($duplicateMessage !== null) {
            return response()->json([
                'status' => false,
                'msg' => $duplicateMessage,
                'type' => 'warning',
            ], 422);
        }

        $warehouseId = $this->resolveWarehouseId();
        if ($warehouseId === null) {
            return response()->json([
                'status' => false,
                'msg' => 'No se encontró un almacén disponible para registrar el producto.',
                'type' => 'warning',
            ], 422);
        }

        $isService = (int) $data['opcion'] === 2;
        $stockActual = $isService ? null : (int) $data['stock_actual'];
        $precioCompra = round((float) $data['precio_compra'], 2);
        $precioVenta = round((float) $data['precio_venta'], 2);
        $igvPercent = $this->resolveIgvPercentByAffectionId((int) $data['idcodigo_igv']);

        $product = Product::create([
            'codigo_interno' => $this->normalizeNullableText($data['codigo_interno'] ?? null),
            'codigo_barras' => $this->normalizeNullableText($data['codigo_barras'] ?? null),
            'codigo_sunat' => $this->normalizeNullableText($data['codigo_sunat'] ?? null),
            'descripcion' => mb_strtoupper(trim((string) $data['descripcion'])),
            'idunidad' => (int) $data['idunidad'],
            'idcategoria' => (int) $data['idcategoria'],
            'igv' => $igvPercent,
            'idcodigo_igv' => (int) $data['idcodigo_igv'],
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta,
            'opcion' => (int) $data['opcion'],
            'stock_actual' => $stockActual,
        ]);

        StockProduct::create([
            'idproducto' => $product->id,
            'idalmacen' => $warehouseId,
            'stock_minimo' => $isService ? null : 10,
            'stock_actual' => $stockActual,
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta,
            'fecha_registro' => now()->toDateString(),
            'stock_entrada' => $stockActual,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos guardados correctamente',
            'type' => 'success',
        ]);
    }

    public function detail(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'title' => 'Espere',
                'type' => 'warning',
            ]);
        }

        $product = Product::query()
            ->leftJoin('categories', 'products.idcategoria', '=', 'categories.id')
            ->select('products.*', 'categories.id as category_id')
            ->where('products.id', (int) $request->input('id'))
            ->first();

        if (! $product) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no existe.',
                'type' => 'warning',
            ], 404);
        }

        if (empty($product->idcodigo_igv)) {
            $product->idcodigo_igv = $this->resolveIgvAffectionIdFromProduct($product);
        }

        return response()->json([
            'status' => true,
            'product' => $product,
        ]);
    }

    public function view_detail(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $id = (int) $request->input('id');
        $product = Product::query()
            ->leftJoin('categories', 'products.idcategoria', '=', 'categories.id')
            ->select('products.*', 'categories.descripcion as categoria')
            ->where('products.id', $id)
            ->first();

        if (! $product) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no existe.',
                'type' => 'warning',
            ], 404);
        }

        $signo = $this->signo_pais();
        $data['codigo'] = empty($product->codigo_interno) ? '-' : $product->codigo_interno;
        $data['codigo_barras'] = empty($product->codigo_barras) ? '-' : $product->codigo_barras;
        $data['descripcion'] = $product->descripcion;
        $data['categoria'] = empty($product->categoria) ? '-' : $product->categoria;
        $data['precio_compra'] = $signo . number_format((float) $product->precio_compra, 2, '.', '');
        $data['precio_venta'] = $signo . number_format((float) $product->precio_venta, 2, '.', '');
        $data['stock'] = $product->stock_actual === null ? '-' : $product->stock_actual;

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
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

        $product = Product::query()->find((int) $request->input('id'));
        if (! $product) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no existe.',
                'type' => 'warning',
            ], 404);
        }

        $validator = $this->validateProductRequest($request, true);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $duplicateMessage = $this->getDuplicateMessage($data, $product->id);
        if ($duplicateMessage !== null) {
            return response()->json([
                'status' => false,
                'msg' => $duplicateMessage,
                'type' => 'warning',
            ], 422);
        }

        $product->update([
            'codigo_interno' => $this->normalizeNullableText($data['codigo_interno'] ?? null),
            'codigo_barras' => $this->normalizeNullableText($data['codigo_barras'] ?? null),
            'codigo_sunat' => $this->normalizeNullableText($data['codigo_sunat'] ?? null),
            'descripcion' => mb_strtoupper(trim((string) $data['descripcion'])),
            'idunidad' => (int) $data['idunidad'],
            'idcategoria' => (int) $data['idcategoria'],
            'igv' => $this->resolveIgvPercentByAffectionId((int) $data['idcodigo_igv']),
            'idcodigo_igv' => (int) $data['idcodigo_igv'],
            'opcion' => (int) $data['opcion'],
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Producto actualizado correctamente',
            'type' => 'success',
        ]);
    }

    public function delete(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'title' => 'Espere',
                'type' => 'warning',
            ]);
        }

        $product = Product::query()->find((int) $request->input('id'));
        if (! $product) {
            return response()->json([
                'status' => false,
                'msg' => 'El producto no existe.',
                'type' => 'warning',
            ], 404);
        }

        if ($this->productHasCommercialUsage($product->id)) {
            return response()->json([
                'status' => false,
                'msg' => 'No se puede eliminar porque el producto ya fue utilizado en otros movimientos del sistema.',
                'type' => 'warning',
            ], 422);
        }

        $hasStock = StockProduct::query()
            ->where('idproducto', $product->id)
            ->where(function ($query) {
                $query->whereNotNull('stock_actual')
                    ->where('stock_actual', '>', 0);
            })
            ->exists();

        if ($hasStock) {
            return response()->json([
                'status' => false,
                'msg' => 'No se puede eliminar porque el producto tiene stock registrado.',
                'type' => 'warning',
            ], 422);
        }

        StockProduct::query()->where('idproducto', $product->id)->delete();
        $product->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente',
            'title' => 'Â¡Bien!',
            'type' => 'success',
        ]);
    }

    private function validateProductRequest(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'descripcion' => 'required|string|max:255',
            'codigo_interno' => 'nullable|string|max:100',
            'codigo_barras' => 'nullable|string|max:100',
            'codigo_sunat' => 'nullable|string|max:16',
            'idunidad' => 'required|integer|exists:units,id',
            'idcategoria' => 'required|integer|exists:categories,id',
            'idcodigo_igv' => 'required|integer|exists:igv_type_affections,id',
            'opcion' => 'required|in:1,2',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'stock_actual' => 'nullable|numeric|min:0',
        ];

        if ($isUpdate) {
            $rules['id'] = 'required|integer|exists:products,id';
        } else {
            $rules['stock_actual'] = 'required_if:opcion,1|nullable|numeric|min:0';
            $rules['precio_compra'] = 'required|numeric|min:0';
            $rules['precio_venta'] = 'required|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules, [
            'descripcion.required' => 'Debe ingresar el nombre del producto o servicio.',
            'idunidad.required' => 'Debe seleccionar la unidad.',
            'idcategoria.required' => 'Debe seleccionar la categoría.',
            'idcodigo_igv.required' => 'Debe seleccionar la afectaciÃ³n IGV.',
            'opcion.required' => 'Debe seleccionar el tipo de item.',
            'precio_compra.required' => 'Debe ingresar el precio de compra.',
            'precio_venta.required' => 'Debe ingresar el precio de venta.',
            'stock_actual.required_if' => 'Debe ingresar el stock inicial para productos.',
        ]);

        $validator->after(function ($validator) use ($request, $isUpdate) {
            $descripcion = trim((string) $request->input('descripcion'));
            if ($descripcion === '') {
                return;
            }

            if ($isUpdate) {
                return;
            }

            if ((int) $request->input('opcion') === 2) {
                return;
            }

            $stock = $request->input('stock_actual');
            if ($stock === null || $stock === '') {
                $validator->errors()->add('stock_actual', 'Debe ingresar el stock inicial para productos.');
            }
        });

        return $validator;
    }

    private function getDuplicateMessage(array $data, ?int $ignoreId = null): ?string
    {
        $productQuery = Product::query();

        if ($ignoreId !== null) {
            $productQuery->where('id', '!=', $ignoreId);
        }

        $codigoInterno = $this->normalizeNullableText($data['codigo_interno'] ?? null);
        if ($codigoInterno !== null && $productQuery->clone()->where('codigo_interno', $codigoInterno)->exists()) {
            return 'Código interno existente';
        }

        $codigoBarras = $this->normalizeNullableText($data['codigo_barras'] ?? null);
        if ($codigoBarras !== null && $productQuery->clone()->where('codigo_barras', $codigoBarras)->exists()) {
            return 'Código de barras existente';
        }

        $descripcion = mb_strtoupper(trim((string) ($data['descripcion'] ?? '')));
        if ($productQuery->clone()->whereRaw('UPPER(descripcion) = ?', [$descripcion])->exists()) {
            return 'Ya existe un producto o servicio con esa descripción';
        }

        return null;
    }

    private function resolveWarehouseId(): ?int
    {
        $userWarehouseId = (int) (Auth::user()->idalmacen ?? 0);
        if ($userWarehouseId > 0 && Warehouse::query()->whereKey($userWarehouseId)->exists()) {
            return $userWarehouseId;
        }

        return Warehouse::query()->orderBy('id')->value('id');
    }

    private function resolveIgvPercentByAffectionId(int $affectionId): int
    {
        $codigo = (string) IgvTypeAffection::query()->whereKey($affectionId)->value('codigo');

        return $codigo === '10' ? 18 : 0;
    }

    private function resolveIgvAffectionIdFromProduct(Product $product): ?int
    {
        if (! empty($product->idcodigo_igv)) {
            return (int) $product->idcodigo_igv;
        }

        $codigo = (int) $product->igv > 0 ? '10' : '20';

        return IgvTypeAffection::query()
            ->where('estado', 1)
            ->where('codigo', $codigo)
            ->value('id');
    }

    private function productHasCommercialUsage(int $productId): bool
    {
        return DetailSaleNote::query()->where('idproducto', $productId)->exists()
            || DetailBuy::query()->where('idproducto', $productId)->exists()
            || DetailBilling::query()->where('idproducto', $productId)->exists()
            || DetailQuote::query()->where('idproducto', $productId)->exists()
            || DetailTransferOrder::query()->where('idproducto', $productId)->exists();
    }

    private function normalizeNullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    public function download()
    {
        $products = Product::query()
            ->select(
                'products.*',
                'units.codigo as unidad_codigo',
                'categories.descripcion as categoria',
                'igv_type_affections.codigo as afectacion_igv_codigo'
            )
            ->join('units', 'units.id', '=', 'products.idunidad')
            ->join('categories', 'categories.id', '=', 'products.idcategoria')
            ->join('igv_type_affections', 'igv_type_affections.id', '=', 'products.idcodigo_igv')
            ->orderBy('products.descripcion')
            ->get();

        return Excel::download(new ProductsCatalogExport($products), 'catalogo_productos.xlsx');
    }

    public function upload(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $excel = $request->file('excel');
        if (empty($excel)) {
            return response()->json([
                'status' => false,
                'msg' => 'Seleccione un documento',
                'type' => 'warning',
            ]);
        }

        if ($excel->extension() !== 'xlsx') {
            return response()->json([
                'status' => false,
                'msg' => 'Seleccione un documento valido en formato .xlsx',
                'type' => 'warning',
            ], 422);
        }

        try {
            Excel::import(new ProductsCatalogImport(), $excel);

            return response()->json([
                'status' => true,
                'msg' => 'El catalogo se actualizo correctamente.',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Se encontraron observaciones en el documento: ' . $e->getMessage(),
                'type' => 'warning',
            ], 422);
        }
    }
}
