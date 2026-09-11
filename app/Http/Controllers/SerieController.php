<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cash;
use App\Models\Serie;
use App\Models\TypeDocument;

class SerieController extends Controller
{
    public function index() {
        $data['type_documents']     = TypeDocument::where('estado', 1)->get();
        $data['cashes']             = Cash::all();
        return view('admin.series.list', $data);
    }

    public function get() {

        $series  = Serie::query()
                ->select('series.*', 'type_documents.descripcion as tipo_documento', 'cashes.descripcion as caja')
                ->join('type_documents', 'series.idtipo_documento', 'type_documents.id')
                ->join('cashes', 'series.idcaja', 'cashes.id')
                ->orderBy('id', 'DESC');

        return Datatables()
                    ->of($series)
                    ->addColumn('acciones', function($series){
                        $id     = $series->id;
                        $btn    = '<div class="dropdown">
                            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        if(!$request->ajax())
        {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $serie                          = trim($request->input('serie'));
        $correlativo                    = trim($request->input('correlativo'));
        $tipo_documento                 = trim($request->input('tipo_documento'));
        $idcaja                         = $request->input('idcaja');
        $idtipo_documento_relacionado   = NULL;
        if((int) $tipo_documento == 6) {   
            $tipo_b_f                   = substr($serie, 0, 2);
            if($tipo_b_f == 'FC')
                $idtipo_documento_relacionado = 1;
            else
                $idtipo_documento_relacionado = 2;
        } else  {
            $idtipo_documento_relacionado = NULL;
        }

        if(strlen($serie) != 4) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Le serie debe contener 04 dígitos',
                'type'      => 'warning'
            ]);
        }

        if(strlen($correlativo) != 8) {
            return response()->json([
                'status'    => false,
                'msg'       => 'El correlativo debe contener 08 dígitos',
                'type'      => 'warning'
            ]);
        }

        $buscar_serie       = Serie::where('serie', mb_strtoupper($serie))->where('idtipo_documento', $tipo_documento)->where('idcaja', $idcaja)->get();
        if(count($buscar_serie) > 0)
        {
            return response()->json([
                'status'    => false,
                'msg'       => 'Serie existente, intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        Serie::insert([
            'serie'                         => mb_strtoupper($serie),
            'correlativo'                   => str_pad($correlativo, STR_PAD_RIGHT),
            'idtipo_documento'              => $tipo_documento,
            'idtipo_documento_relacionado'  => $idtipo_documento_relacionado,
            'idcaja'                        => $idcaja
        ]);

        return response()->json([
            'status'    => true,
            'msg'       => 'Datos guardados correctamente',
            'type'      => 'success'
        ]);
    }

    public function detail(Request $request) {
        if(!$request->ajax())
        {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        $id       = $request->input('id');
        $serie    = Serie::where('id', $id)->first();
        return response()->json(['status'  => true, 'serie' => $serie]);
    }

    public function store(Request $request) {
        if(!$request->ajax()) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        $id                             = $request->input('id');
        $serie                          = trim($request->input('serie'));
        $correlativo                    = trim($request->input('correlativo'));
        $tipo_documento                 = trim($request->input('tipo_documento'));
        $idcaja                         = $request->input('idcaja');

        $idtipo_documento_relacionado   = NULL;
        
        if((int) $tipo_documento == 6) {   
            $tipo_b_f                   = substr($serie, 0, 2);
            if($tipo_b_f == 'FC')
                $idtipo_documento_relacionado = 1;
            else
                $idtipo_documento_relacionado = 2;
        } else {
            $idtipo_documento_relacionado = NULL;
        }

        if(strlen($serie) != 4) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Le serie debe contener 04 dígitos',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        if(strlen($correlativo) != 8) {
            return response()->json([
                'status'    => false,
                'msg'       => 'El correlativo debe contener 08 dígitos',
                'title'     => 'Espere',
                'type'      => 'warning'
            ]);
        }

        try {
            Serie::where('id', $id)->update([
                'serie'                        => mb_strtoupper($serie),
                'correlativo'                  => str_pad($correlativo, 8, '0', STR_PAD_LEFT),
                'idtipo_documento'             => $tipo_documento,
                'idtipo_documento_relacionado' => $idtipo_documento_relacionado,
                'idcaja'                       => $idcaja
            ]);
        
            return response()->json([
                'status' => true,
                'msg'    => 'Datos actualizados correctamente',
                'type'   => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg'    => 'Error al actualizar los datos: ' . $e->getMessage(),
                'type'   => 'error'
            ]);
        }
        
    }

    public function delete(Request $request)
    {
        if(!$request->ajax())  {
            return response()->json([
                'status'    => false,
                'msg'       => 'Intente de nuevo',
                'type'      => 'warning'
            ]);
        }

        $id            = $request->input('id');
        Serie::where('id', $id)->delete();

        return response()->json([
            'status'    => true,
            'msg'       => 'Registro eliminado correctamente',
            'type'      => 'success'
        ]);
    }
}
