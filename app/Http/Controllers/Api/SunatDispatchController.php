<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Services\Ebilling\SunatDispatchService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class SunatDispatchController extends Controller
{
    public function __construct(
        private readonly SunatDispatchService $service,
    ) {
    }

    public function __invoke(Billing $billing): JsonResponse
    {
        try {
            $result = $this->service->dispatch($billing);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }
}
