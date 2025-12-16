<?php
namespace Naimul007A\LaravelBaseKit\Exceptions;

use Exception;

class ApiException extends Exception {
    protected int $statusCode;
    protected string $errorMessage;

    public function __construct(string $message, int $statusCode = 400) {
        parent::__construct($message);
        $this->statusCode   = $statusCode;
        $this->errorMessage = $message;
    }

    public function render(): \Illuminate\Http\JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $this->errorMessage,
        ], $this->statusCode);
    }
}
