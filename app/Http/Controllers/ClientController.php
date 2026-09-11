<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\District;
use App\Models\IdentityDocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        $typeDocuments = IdentityDocumentType::query()
            ->where('estado', 1)
            ->orderByRaw("CASE codigo WHEN '1' THEN 1 WHEN '6' THEN 2 WHEN '4' THEN 3 ELSE 99 END")
            ->orderBy('descripcion')
            ->get();

        return view('admin.clients.list', compact('typeDocuments'));
    }

    public function get(Request $request)
    {
        $clients = Client::query()
            ->with('tipoDocumento:id,codigo,descripcion')
            ->when($request->filled('filter_name'), function ($query) use ($request) {
                $query->where('nombres', 'like', '%' . trim((string) $request->input('filter_name')) . '%');
            })
            ->when($request->filled('filter_document'), function ($query) use ($request) {
                $query->where('nro_documento', 'like', '%' . trim((string) $request->input('filter_document')) . '%');
            })
            ->orderByDesc('id');

        return datatables()
            ->of($clients)
            ->addColumn('documento_info', function (Client $client) {
                return '<span class="fw-semibold">' . e((string) $client->nro_documento) . '</span>';
            })
            ->addColumn('acciones', function (Client $client) {
                return '<div class="dropdown">
                            <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item btn-detail" data-id="' . $client->id . '" href="javascript:void(0);">Actualizar</a>
                                <a class="dropdown-item btn-confirm" data-id="' . $client->id . '" href="javascript:void(0);">Eliminar</a>
                            </div>
                        </div>';
            })
            ->rawColumns(['documento_info', 'acciones'])
            ->toJson();
    }

    public function searchDocument(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $validator = Validator::make($request->all(), [
            'type_document' => 'required|integer',
            'dni_ruc' => 'required|string|max:15',
        ], [
            'type_document.required' => 'Debe seleccionar un tipo de documento.',
            'dni_ruc.required' => 'Debe ingresar el numero de documento.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $typeDocument = IdentityDocumentType::query()
            ->where('id', (int) $request->input('type_document'))
            ->orWhere('codigo', (string) $request->input('type_document'))
            ->first(['id', 'codigo']);

        if (! $typeDocument) {
            return response()->json(['status' => false, 'msg' => 'Tipo de documento invalido.', 'type' => 'warning'], 422);
        }

        $typeCode = trim((string) $typeDocument->codigo);
        $dniRuc = trim((string) $request->input('dni_ruc'));

        if ($typeCode === '1' && strlen($dniRuc) !== 8) {
            return response()->json(['status' => false, 'msg' => 'Para DNI debe ingresar 8 digitos.', 'type' => 'warning'], 422);
        }

        if ($typeCode === '6' && strlen($dniRuc) !== 11) {
            return response()->json(['status' => false, 'msg' => 'Para RUC debe ingresar 11 digitos.', 'type' => 'warning'], 422);
        }

        $document = $this->verify__client($dniRuc);

        if (! isset($document->status) || (int) $document->status === 404) {
            return response()->json([
                'status' => false,
                'msg' => $document->message ?? 'No se pudo obtener la informacion del documento.',
                'type' => 'warning',
            ], 404);
        }

        $data = $document->data ?? null;

        if (! $data) {
            return response()->json([
                'status' => false,
                'msg' => 'No se encontro informacion para el documento consultado.',
                'type' => 'warning',
            ], 404);
        }

        if ($typeCode === '1') {
            $names = trim(($data->nombres ?? '') . ' ' . ($data->apellido_paterno ?? '') . ' ' . ($data->apellido_materno ?? ''));
            $address = empty($data->direccion) ? '-' : $data->direccion;
            $ubigeo = $data->ubigeo_sunat ?? null;
        } else {
            $names = $data->nombre_o_razon_social ?? '';
            $address = empty($data->direccion) ? '-' : $data->direccion;
            $ubigeo = (($data->ubigeo_sunat ?? '') === '-' || empty($data->ubigeo_sunat)) ? null : $data->ubigeo_sunat;
        }

        return response()->json([
            'status' => true,
            'nombres' => trim((string) $names),
            'direccion' => trim((string) $address),
            'ubigeo' => $ubigeo,
        ]);
    }

    public function save(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $validator = $this->validateClientRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $exists = Client::query()
            ->where('iddoc', $data['tipo_documento'])
            ->where('nro_documento', trim((string) $data['dni_ruc']))
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'msg' => 'El cliente ya se encuentra registrado.',
                'type' => 'warning',
            ], 422);
        }

        $client = Client::create([
            'iddoc' => $data['tipo_documento'],
            'nro_documento' => trim((string) $data['dni_ruc']),
            'nombres' => trim((string) $data['razon_social']),
            'direccion' => trim((string) $data['direccion']),
            'codigo_pais' => 'PE',
            'ubigeo' => $this->resolveUbigeo($data),
            'telefono' => $this->normalizeNullableText($data['telefono'] ?? null),
            'email' => $this->normalizeNullableText($data['email'] ?? null),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos guardados correctamente.',
            'type' => 'success',
            'last_id' => $client->id,
        ]);
    }

    public function detail(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $client = Client::query()->find((int) $request->input('id'));

        if (! $client) {
            return response()->json(['status' => false, 'msg' => 'El cliente no existe.', 'type' => 'warning'], 404);
        }

        $departmentCode = null;
        $provinceCode = null;
        $districtCode = null;

        if (! empty($client->ubigeo)) {
            $district = District::query()->where('codigo', $client->ubigeo)->first();
            if ($district) {
                $districtCode = $district->codigo;
                $provinceCode = $district->provincia_codigo;
                $departmentCode = $district->departamento_codigo;
            }
        }

        return response()->json([
            'status' => true,
            'client' => $client,
            'department_code' => $departmentCode,
            'province_code' => $provinceCode,
            'district_code' => $districtCode,
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $client = Client::query()->find((int) $request->input('id'));

        if (! $client) {
            return response()->json(['status' => false, 'msg' => 'El cliente no existe.', 'type' => 'warning'], 404);
        }

        $validator = $this->validateClientRequest($request, true);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $data = $validator->validated();
        $exists = Client::query()
            ->where('iddoc', $data['tipo_documento'])
            ->where('nro_documento', trim((string) $data['dni_ruc']))
            ->where('id', '!=', $client->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'msg' => 'Ya existe otro cliente con ese documento.',
                'type' => 'warning',
            ], 422);
        }

        $client->update([
            'iddoc' => $data['tipo_documento'],
            'nro_documento' => trim((string) $data['dni_ruc']),
            'nombres' => trim((string) $data['razon_social']),
            'direccion' => trim((string) $data['direccion']),
            'codigo_pais' => 'PE',
            'ubigeo' => $this->resolveUbigeo($data),
            'telefono' => $this->normalizeNullableText($data['telefono'] ?? null),
            'email' => $this->normalizeNullableText($data['email'] ?? null),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente.',
            'type' => 'success',
        ]);
    }

    public function delete(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json(['status' => false, 'msg' => 'Intente de nuevo', 'type' => 'warning']);
        }

        $client = Client::query()->find((int) $request->input('id'));

        if (! $client) {
            return response()->json(['status' => false, 'msg' => 'Cliente no encontrado.', 'type' => 'warning'], 404);
        }

        $client->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Registro eliminado correctamente.',
            'type' => 'success',
        ]);
    }

    private function validateClientRequest(Request $request, bool $isUpdate = false)
    {
        $rules = [
            'tipo_documento' => 'required|integer|exists:identity_document_types,id',
            'dni_ruc' => 'required|string|max:15',
            'razon_social' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:2',
            'provincia' => 'nullable|string|max:4',
            'distrito' => 'nullable|string|max:6',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ];

        if ($isUpdate) {
            $rules['id'] = 'required|integer';
        }

        $validator = Validator::make($request->all(), $rules, [
            'tipo_documento.required' => 'Debe seleccionar el tipo de documento.',
            'dni_ruc.required' => 'Debe ingresar el numero del documento.',
            'razon_social.required' => 'Debe ingresar el nombre o razon social.',
            'direccion.required' => 'Debe ingresar la direccion.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $message = $this->validateDocumentNumberByType(
                (int) $request->input('tipo_documento'),
                (string) $request->input('dni_ruc')
            );

            if ($message !== null) {
                $validator->errors()->add('dni_ruc', $message);
            }
        });

        return $validator;
    }

    private function resolveUbigeo(array $data): ?string
    {
        $tipoDocumentoId = (int) ($data['tipo_documento'] ?? 0);
        $tipoDocumentoCodigo = trim((string) IdentityDocumentType::query()
            ->whereKey($tipoDocumentoId)
            ->value('codigo'));
        $departamento = trim((string) ($data['departamento'] ?? ''));
        $distrito = trim((string) ($data['distrito'] ?? ''));

        if (in_array($tipoDocumentoCodigo, ['1', '4', '6'], true) && $departamento !== '') {
            return $distrito !== '' ? $distrito : null;
        }

        return null;
    }

    private function validateDocumentNumberByType(int $tipoDocumentoId, string $documentNumber): ?string
    {
        $tipoDocumentoCodigo = trim((string) IdentityDocumentType::query()
            ->whereKey($tipoDocumentoId)
            ->value('codigo'));
        $documentNumber = trim($documentNumber);

        if ($tipoDocumentoCodigo === '1' && ! preg_match('/^\d{8}$/', $documentNumber)) {
            return 'Para DNI debe ingresar exactamente 8 digitos numericos.';
        }

        if ($tipoDocumentoCodigo === '6' && ! preg_match('/^\d{11}$/', $documentNumber)) {
            return 'Para RUC debe ingresar exactamente 11 digitos numericos.';
        }

        if (in_array($tipoDocumentoCodigo, ['4', '7', 'A', '0'], true) && ! preg_match('/^[A-Za-z0-9\-]{3,15}$/', $documentNumber)) {
            return 'Para este tipo de documento solo se permiten letras, numeros y guion.';
        }

        return null;
    }

    private function normalizeNullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
