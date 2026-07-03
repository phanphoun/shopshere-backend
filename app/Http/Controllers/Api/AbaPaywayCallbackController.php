<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbaPaywayCallbackController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('ABA Payway callback received', $payload);

        $transactionId = (string) ($payload['tran_id'] ?? '');
        $status = (string) ($payload['status'] ?? '');
        $merchantRefNo = (string) ($payload['merchant_ref_no'] ?? '');

        if ($status === '00' && $transactionId !== '') {
            if ($merchantRefNo !== '') {
                $order = \App\Models\Order::where('order_number', $merchantRefNo)->first();

                if ($order && $order->payment_status !== \App\Models\Order::PAYMENT_PAID) {
                    $this->orderService->markAsPaid($order);
                    $order->update([
                        'aba_payway_txn_id' => $transactionId,
                        'paid_at' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback processed.',
        ]);
    }
}
