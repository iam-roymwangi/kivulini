<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MpesaException extends Exception
{
    public function __construct(
        string $message = 'Unable to initiate M-Pesa payment. Please try again.',
        public readonly ?int $status = null,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'status' => $this->status,
        ];
    }

    public function render(Request $request): ?JsonResponse
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return response()->json([
            'message' => $this->getMessage(),
        ], 502);
    }
}
