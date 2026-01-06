<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentPackage;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PayOS\PayOS;

class PaymentPackageController extends Controller
{
    public function index()
    {
        $packages = PaymentPackage::where('status', 1)
            ->orderBy('display_order', 'asc')
            ->get();
        return response()->json($packages);
    }

    // thanh toán
    public function create(Request $request)
    {
        $user = auth()->user();
        $package = PaymentPackage::findOrFail($request->package_id);

        $orderCode = (int) (now()->timestamp . random_int(1000, 9999));

        $payment = Payment::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'order_code' => $orderCode,
            'amount' => $package->final_price,
            'status' => 'pending',
        ]);

        $payOS = new PayOS(
            config('payos.client_id'),
            config('payos.api_key'),
            config('payos.checksum_key')
        );

        $data = [
            "orderCode" => (int)$orderCode,
            "amount" => (int)$package->final_price,
            "description" => "Nạp cú",
            'returnUrl' => env('PAYOS_RETURN_URL') . '/payment/success/' . $orderCode,
            'cancelUrl' => env('PAYOS_RETURN_URL') . '/payment/cancel/' . $orderCode,
        ];

        $response = $payOS->createPaymentLink($data);

        return response()->json([
            'checkoutUrl' => $response['checkoutUrl'],
        ]);
    }

    // Xác nhận thanh toán thành công
    public function success(Request $request, $orderCode)
    {
        $payment = Payment::where('order_code', $orderCode)->firstOrFail();

        if ($payment->status === 'success') {
            return response()->json(['message' => 'Thanh toán đã được xác nhận trước đó']);
        }

        $payOS = new PayOS(
            config('payos.client_id'),
            config('payos.api_key'),
            config('payos.checksum_key')
        );

        $payosData = $payOS->getPaymentLinkInformation((int)$orderCode);

        if (($payosData['status'] ?? null) !== 'PAID') {
            return response()->json([
                'message' => 'Thanh toán không thành công'
            ], 400);
        }

        DB::transaction(function () use ($payment, $payosData) {
            $user = $payment->user;

            $before = $user->coin;
            $after  = $before + $payment->amount;

            $payment->update([
                'status'     => 'success',
                'paid_at'    => now(),
                'payos_data' => $payosData,
            ]);

            $user->update(['coin' => $after]);

            WalletHistory::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'type' => 'deposit',
                'amount' => $payment->amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => 'Nạp coin qua PayOS',
            ]);
        });

        return response()->json(['message' => 'Thanh toán thành công']);
    }


    // Xử lý thanh toán thất bại
    public function cancel(Request $request, $orderCode)
    {
        $payment = Payment::where('order_code', $orderCode)->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found'
            ], 404);
        }

        if ($payment->status === 'pending') {
            $payment->update([
                'status' => 'canceled'
            ]);
        }

        return response()->json([
            'message' => 'Hủy thanh toán thành công',
            'status'  => 'canceled'
        ]);
    }
}
