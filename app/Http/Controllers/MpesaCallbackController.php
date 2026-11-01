<?php

namespace App\Http\Controllers;

use App\Actions\CompleteMpesaPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    public function store(Request $request, string $secret, CompleteMpesaPayment $completeMpesaPayment): JsonResponse
    {
        $expected = (string) config('mpesa.callback_secret');

        if ($expected === '' || ! hash_equals($expected, $secret)) {
            abort(404);
        }

        $completeMpesaPayment->fromCallback($request->all());

        Log::info('M-Pesa STK callback received', [
            'checkout_request_id' => data_get($request->all(), 'Body.stkCallback.CheckoutRequestID'),
            'result_code' => data_get($request->all(), 'Body.stkCallback.ResultCode'),
        ]);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
