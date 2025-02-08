<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (Throwable $e) {
            // Handle ModelNotFoundException (404 Not Found)
            if ($e instanceof ModelNotFoundException) {
                return (new ApiException(
                    'Record not found',
                    ['id' => ['The requested resource was not found']],
                    404
                ))->render();
            }

            if ($e instanceof AuthenticationException) {
                return (new ApiException(
                    'Autohentication failed',
                    ['id' => ['authenticate failed']],
                    401
                ))->render();
            }

            // Handle ValidationException (422 Unprocessable Entity)
            if ($e instanceof ValidationException) {
                return (new ApiException(
                    'Validation failed',
                    $e->errors(),
                    422
                ))->render();
            }

            // Handle any other exception (500 Internal Server Error)
            if (!config('app.debug')) {
                return (new ApiException(
                    'Server Error',
                    ['server' => ['An unexpected error occurred']],
                    500
                ))->render();
            }

            return parent::render(request(), $e);
        });
    }

}
