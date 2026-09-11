<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;


class CategoryController extends Controller
{
    public function index() {
        return view('admin.categories.list');
    }

    public function get(Request $request) {
        $categories     = Category::query()->orderBy('id', 'DESC');


        return Datatables()
            ->of($categories)
            ->addColumn('acciones', function ($categories) {
                $id     = $categories->id;
                $btn    = '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink1" style="">
                                    <a class="dropdown-item btn-detail" data-id="'.$id.'" href="javascript:void(0);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit menu-icon"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span> Actualizar</span>
                                </a>
                                    <a class="dropdown-item btn-confirm" data-id="'.$id.'" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 menu-icon"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    <span> Eliminar</span>
                            </a>
                            </div>
                            </div>';
                return $btn;
            })
            ->rawColumns(['acciones'])
            ->toJson();
    }

    public function save(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }
        
        $descripcion        = trim($request->input('descripcion'));
        $buscar_categoria   = Category::where('descripcion', mb_strtoupper($descripcion))->first();
        if (!empty($buscar_categoria)) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Categoría existente con esos datos',
                'type'      => 'warning'
            ]);
        }

        Category::insert([
            'descripcion'   => mb_strtoupper($descripcion)
        ]);

        $last_id            = Category::latest('id')->first()['id'];

        return response()->json([
            'status'        => true,
            'msg'           => 'Datos guardados correctamente',
            'type'          => 'success',
            'categories'    => Category::orderBy('id', 'DESC')->get(),
            'last_id'       => $last_id
        ]);
    }

    public function detail(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }
  
        $id             = $request->input('id');
        $category       = Category::where('id', $id)->first();
        return response()->json(['status'  => true, 'category' => $category]);
    }

    public function store(Request $request) {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id                 = $request->input('id');
        $descripcion        = trim($request->input('descripcion'));
        Category::where('id', $id)->update([
            'descripcion'  => mb_strtoupper($descripcion)
        ]);

        return response()->json([
            'status'    => true,
            'msg'       => 'Datos actualizados correctamente',
            'type'      => 'success'
        ]);
    }

    public function delete(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id                     = $request->input('id');
        $buscar_algun_producto  = Product::where('idcategoria', $id)->first();

        if($buscar_algun_producto) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Existen productos registrados con esta categoría',
                'type'      => 'warning'
            ]);
        }

        Category::where('id', $id)->delete();
        return response()->json([
            'status'    => true,
            'msg'       => 'Registro eliminado correctamente',
            'type'      => 'success'
        ]);
    }
}
