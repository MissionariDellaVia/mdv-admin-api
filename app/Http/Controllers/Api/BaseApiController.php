<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function sendResponse($data, int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    protected function sendError($message, $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'metadata' => [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'user' => auth()->user()?->name ?? 'admin'
            ]
        ], $code);
    }
}
