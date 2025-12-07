<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'successMessage' => $message,
            'statusCode' => $statusCode,
        ] , $statusCode);
    }

    protected function dataResponse(mixed $data = null , int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'statusCode' => $statusCode,
        ] , $statusCode);
    }

    protected function errorResponse(string $message = '', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'errorMessage' => $message,
            'statusCode' => $statusCode,
        ],$statusCode);
    }

    protected function paginatedResponse($paginator , string $message = '' , int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
            'statusCode' => $statusCode,
        ] , $statusCode);
    }
}
