<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Country;
use App\Models\Department;
use App\Models\District;
use App\Models\Province;
use App\Services\Ebilling\Support\SunatServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BusinessController extends Controller
{
    public function index()
    {
        $empresa = Business::query()->findOrFail(1);

        return view('admin.business.home', compact('empresa'));
    }

    public function load_logo(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $empresa = Business::query()->find(1);

        return response()->json([
            'status' => true,
            'empresa' => $empresa,
            'logo_url' => $this->resolveBusinessLogoUrl($empresa),
        ]);
    }

    public function load_ubigeo(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $empresa = Business::query()->findOrFail(1);
        $ubigeo = $empresa->ubigeo;
        $departments = Department::all();
        $department = null;
        $province = null;
        $district = null;
        $provinces = collect();
        $districts = collect();

        if (! empty($ubigeo)) {
            $district = District::where('codigo', $ubigeo)->first();

            if ($district) {
                $province = Province::where('codigo', $district->provincia_codigo)->first();
                $department = Department::where('codigo', $district->departamento_codigo)->first();

                if ($department) {
                    $provinces = Province::where('departamento_codigo', $department->codigo)->get();
                }

                if ($department && $province) {
                    $districts = District::where('departamento_codigo', $department->codigo)
                        ->where('provincia_codigo', $province->codigo)
                        ->get();
                }
            }
        }

        return response()->json([
            'ubigeo' => $ubigeo,
            'departments' => $departments,
            'provinces' => $provinces,
            'districts' => $districts,
            'department' => $department,
            'province' => $province,
            'district' => $district,
        ]);
    }

    public function load_provinces(Request $request)
    {
        $codigo = $request->input('codigo');
        $provinces = Province::where('departamento_codigo', $codigo)->get();

        return response()->json([
            'provinces' => $provinces,
        ]);
    }

    public function load_districts(Request $request)
    {
        $codigo = $request->input('codigo');
        $departamentoCodigo = $request->input('codigo_departamento');
        $province = Province::where('codigo', $codigo)->first();

        $districts = collect();

        if ($province) {
            $districts = District::where('departamento_codigo', $departamentoCodigo)
                ->where('provincia_codigo', $province->codigo)
                ->get();
        }

        return response()->json([
            'districts' => $districts,
        ]);
    }

    public function save_info(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'ruc' => 'required|string|size:11',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:2',
            'provincia' => 'nullable|string|max:4',
            'distrito' => 'nullable|string|max:6',
            'urbanizacion' => 'nullable|string|max:255',
            'local' => 'nullable|string|max:50',
        ], [
            'ruc.required' => 'Debes ingresar el RUC de la empresa.',
            'ruc.size' => 'El RUC debe tener 11 digitos.',
            'razon_social.required' => 'Debes ingresar la razon social.',
            'direccion.required' => 'Debes ingresar la direccion fiscal.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        $empresa = Business::query()->findOrFail(1);
        $logo = $request->file('logo');
        $logoName = $empresa->logo;

        if ($logo) {
            $logoName = Str::slug((string) $request->input('razon_social')) . '-' . now()->format('YmdHis') . '.' . strtolower((string) $logo->getClientOriginalExtension());
            File::ensureDirectoryExists(public_path('files/logos'));
            $logo->move(public_path('files/logos'), $logoName);
        }

        $peru = Country::query()->where('prefijo', 'PE')->first();

        $empresa->update([
            'ruc' => trim((string) $request->input('ruc')),
            'razon_social' => mb_strtoupper(trim((string) $request->input('razon_social'))),
            'nombre_comercial' => mb_strtoupper(trim((string) $request->input('nombre_comercial'))),
            'logo' => $logoName,
            'idpais' => $peru?->id ?? $empresa->idpais,
            'direccion' => mb_strtoupper(trim((string) $request->input('direccion'))),
            'codigo_pais' => 'PE',
            'ubigeo' => $request->filled('distrito') ? $request->input('distrito') : $empresa->ubigeo,
            'urbanizacion' => mb_strtoupper(trim((string) $request->input('urbanizacion'))),
            'local' => mb_strtoupper(trim((string) $request->input('local'))),
            'telefono' => trim((string) $request->input('telefono')),
            'cobrar_igv' => $request->boolean('cobrar_igv'),
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Datos actualizados correctamente',
            'type' => 'success',
            'logo_url' => $this->resolveBusinessLogoUrl($empresa) . '?v=' . time(),
        ]);
    }

    public function save_sunat(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'servidor_sunat' => 'nullable|in:1,3',
            'gre_client_id' => 'nullable|string|max:100',
            'gre_client_secret' => 'nullable|string|max:150',
            'certificado' => [
                'nullable',
                'file',
                'max:4096',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! $value) {
                        return;
                    }

                    if (strtolower((string) $value->getClientOriginalExtension()) !== 'pfx') {
                        $fail('El certificado debe estar en formato .pfx.');
                    }
                },
            ],
        ], [
            'servidor_sunat.in' => 'El servidor SUNAT debe ser 1 para produccion o 3 para beta.',
            'certificado.max' => 'El certificado no debe superar los 4MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'errors' => $validator->errors(),
                'type' => 'warning',
            ], 422);
        }

        if ($request->filled('servidor_sunat')) {
            try {
                SunatServer::normalize($request->input('servidor_sunat'));
            } catch (InvalidArgumentException $exception) {
                return response()->json([
                    'status' => false,
                    'msg' => $exception->getMessage(),
                    'type' => 'warning',
                ], 422);
            }
        }

        $empresa = Business::query()->findOrFail(1);
        $certificate = $request->file('certificado');
        $certificatePath = $empresa->certificado;

        if ($certificate) {
            $directory = public_path('api_sunat');
            $fileName = trim((string) $empresa->ruc) . '.pfx';
            File::ensureDirectoryExists($directory);
            $certificate->move($directory, $fileName);
            $certificatePath = 'api_sunat/' . $fileName;
        }

        $empresa->update([
            'nombre_comercial' => mb_strtoupper(trim((string) $request->input('nombre_comercial'))),
            'usuario_sunat' => trim((string) $request->input('usuario_sunat')),
            'clave_sunat' => trim((string) $request->input('clave_sunat')),
            'clave_certificado' => trim((string) $request->input('clave_certificado')),
            'vencimiento_certificado' => $request->input('vencimiento_certificado') ?: null,
            'servidor_sunat' => $request->input('servidor_sunat'),
            'gre_client_id' => trim((string) $request->input('gre_client_id')),
            'gre_client_secret' => trim((string) $request->input('gre_client_secret')),
            'certificado' => $certificatePath,
        ]);

        return response()->json([
            'status' => true,
            'msg' => $certificate
                ? 'Credenciales actualizadas y certificado cargado correctamente.'
                : 'Datos actualizados correctamente',
            'type' => 'success',
            'certificate_name' => !empty($certificatePath) ? basename((string) $certificatePath) : null,
        ]);
    }

    private function resolveBusinessLogoUrl(?Business $business): string
    {
        if (! $business || blank($business->logo)) {
            return asset('files/empty_logo.png');
        }

        return asset('files/logos/' . ltrim((string) $business->logo, '/'));
    }
}
