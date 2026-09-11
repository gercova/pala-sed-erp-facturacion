<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Services\Ebilling\Payload\BillingPayloadBuilder;
use App\Services\Ebilling\Validation\PayloadValidator;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class SunatBillingPayloadController extends Controller
{
    public function __construct(
        private readonly BillingPayloadBuilder $builder,
        private readonly PayloadValidator $validator,
    ) {
    }

    public function __invoke(Billing $billing): JsonResponse
    {
        try {
            $payload = $this->builder->build($billing);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $validation = $this->validator->validate($payload);

        return response()->json([
            'ok' => $validation->isValid(),
            'message' => $validation->isValid()
                ? 'Payload generado correctamente desde el comprobante.'
                : 'Se genero el payload, pero no paso las validaciones.',
            'billing_id' => $billing->id,
            'payload' => $payload,
            'errors' => $validation->errors(),
        ], $validation->isValid() ? 200 : 422);
    }
}
