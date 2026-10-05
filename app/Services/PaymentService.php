<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /** VNPay: tạo URL thanh toán có chữ ký HMAC-SHA512 */
    public function vnpayUrl(PaymentTransaction $tx, string $ip): string
    {
        $cfg = config('payment.vnpay');
        $params = [
            'vnp_Version' => '2.1.0', 'vnp_Command' => 'pay', 'vnp_TmnCode' => $cfg['tmn_code'],
            'vnp_Amount' => $tx->amount * 100, 'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $tx->transaction_code, 'vnp_OrderInfo' => 'Thanh toan don hang '.$tx->order->code,
            'vnp_OrderType' => 'other', 'vnp_Locale' => 'vn', 'vnp_ReturnUrl' => $cfg['return_url'],
            'vnp_IpAddr' => $ip, 'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_ExpireDate' => now()->addMinutes(config('payment.expire_minutes'))->format('YmdHis'),
        ];
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC1738);

        return $cfg['url'].'?'.$query.'&vnp_SecureHash='.hash_hmac('sha512', $query, $cfg['hash_secret']);
    }

    /** Kiểm tra chữ ký trả về từ VNPay (dùng cho cả Return URL và IPN) */
    public function verifyVnpay(array $input): bool
    {
        $hash = $input['vnp_SecureHash'] ?? '';
        $data = collect($input)->filter(fn ($v, $k) => str_starts_with($k, 'vnp_') && ! in_array($k, ['vnp_SecureHash', 'vnp_SecureHashType']))
            ->sortKeys()->all();
        $expected = hash_hmac('sha512', http_build_query($data, '', '&', PHP_QUERY_RFC1738), (string) config('payment.vnpay.hash_secret'));

        return hash_equals($expected, $hash);
    }

    /**
     * Ghi nhận kết quả thanh toán MỘT LẦN DUY NHẤT (idempotent).
     * Trả về: ok | already | not_found | wrong_amount
     */
    public function settle(string $code, int $amount, bool $success, ?string $gatewayNo, array $raw): string
    {
        return DB::transaction(function () use ($code, $amount, $success, $gatewayNo, $raw) {
            $tx = PaymentTransaction::where('transaction_code', $code)->lockForUpdate()->first();
            if (! $tx) {
                return 'not_found';
            }
            if ((int) $tx->amount !== $amount) {
                return 'wrong_amount';
            }
            if ($tx->isFinal()) {
                return 'already'; // IPN gửi lặp -> bỏ qua
            }

            $tx->update([
                'status' => $success ? 'success' : 'failed',
                'gateway_transaction_no' => $gatewayNo,
                'raw_response' => $raw,
                'paid_at' => $success ? now() : null,
            ]);

            $order = $tx->order;
            if ($success) {
                $order->update(['payment_status' => 'paid', 'status' => $order->status === 'pending' ? 'confirmed' : $order->status]);
            } else {
                $order->update(['payment_status' => 'failed']);
            }

            return 'ok';
        });
    }
}
