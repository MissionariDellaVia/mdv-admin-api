<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected array $errors;

    public function __construct(string $message = "", array $errors = [], int $code = 404)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
            'metadata' => [
                'timestamp' => '2025-01-30 17:30:13',
                'user' => 'Alessandro-Mac7'
            ]
        ], $this->getCode());
    }
}
