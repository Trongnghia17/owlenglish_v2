<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use PayOS\PayOS;
use App\Models\Payment;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayosWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payOS = new PayOS(
            config('payos.client_id'),
            config('payos.api_key'),
            config('payos.checksum_key')
        );

        // Verify signature
        $data = $payOS->verifyPaymentWebhookData($request->all());

        if (!$data) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        DB::transaction(function () use ($data) {
            $payment = Payment::where('order_code', $data['orderCode'])->lockForUpdate()->first();

            if (!$payment || $payment->status === 'success') {
                return;
            }

            if ($data['status'] === 'PAID') {
                $user = $payment->user;

                $before = $user->coin;
                $after = $before + $payment->amount;

                $payment->update([
                    'status' => 'success',
                    'payos_payment_id' => $data['paymentLinkId'] ?? null,
                    'payos_data' => $data,
                    'paid_at' => now(),
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
            } else {
                $payment->update([
                    'status' => 'failed',
                    'payos_data' => $data,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}