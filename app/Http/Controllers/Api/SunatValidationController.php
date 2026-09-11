<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Ebilling\Support\BusinessProfile;
use App\Services\Ebilling\Support\SunatServer;
use App\Services\Ebilling\Validation\PayloadValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SunatValidationController extends Controller
{
    public function __construct(
        private readonly PayloadValidator $validator,
        private readonly BusinessProfile $businessProfile,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        if ($payload === []) {
            $payload = $request->all();
        }

        if (! is_array($payload) || $payload === []) {
            return response()->json([
                'ok' => false,
                'message' => 'Debe enviar un payload JSON valido.',
                'errors' => [
                    ['field' => 'payload', 'message' => 'No se recibieron datos para validar.'],
                ],
            ], 422);
        }

        $business = Business::query()->find(1);

        if (! $business) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe una empresa configurada para validar el payload.',
            ], 404);
        }

        try {
            SunatServer::normalize($business->servidor_sunat);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'La configuracion SUNAT de la empresa no es valida.',
                'errors' => [
                    ['field' => 'business.servidor_sunat', 'message' => $exception->getMessage()],
                ],
            ], 422);
        }

        $result = $this->validator->validate($payload);

        return response()->json([
            'ok' => $result->isValid(),
            'message' => $result->isValid()
                ? 'Payload valido. Aun no se ha ejecutado el envio a SUNAT.'
                : 'El payload no paso las validaciones.',
            'business' => $this->businessProfile->fromModel($business),
            'errors' => $result->errors(),
        ], $result->isValid() ? 200 : 422);
    }
}
