<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payment) {}

    /** Return URL: CHỈ hiển thị kết quả cho khách, không cập nhật DB (nguồn sự thật là IPN) */
    public function vnpayReturn(Request $request)
    {
        $tx = PaymentTransaction::with('order')->where('transaction_code', $request->query('vnp_TxnRef'))->firstOrFail();
        $valid = $this->payment->verifyVnpay($request->query());

        // Môi trường local không nhận được IPN -> khi chữ ký hợp lệ vẫn gọi settle (idempotent nên an toàn)
        if ($valid && ! $tx->isFinal()) {
            $this->payment->settle($tx->transaction_code, (int) $request->query('vnp_Amount') / 100,
                $request->query('vnp_ResponseCode') === '00', $request->query('vnp_TransactionNo'), $request->query());
        }

        return redirect()->route('order.done', $tx->order->code)
            ->with($valid ? 'success' : 'error', __($valid ? 'Đã nhận kết quả thanh toán.' : 'Chữ ký không hợp lệ!'));
    }

    /** IPN: VNPay gọi server-to-server (GET). Phải trả về JSON RspCode. */
    public function vnpayIpn(Request $request)
    {
        if (! $this->payment->verifyVnpay($request->query())) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }
        $result = $this->payment->settle($request->query('vnp_TxnRef'), (int) $request->query('vnp_Amount') / 100,
            $request->query('vnp_ResponseCode') === '00', $request->query('vnp_TransactionNo'), $request->query());

        return response()->json(match ($result) {
            'ok' => ['RspCode' => '00', 'Message' => 'Confirm Success'],
            'already' => ['RspCode' => '02', 'Message' => 'Order already confirmed'],
            'wrong_amount' => ['RspCode' => '04', 'Message' => 'Invalid amount'],
            default => ['RspCode' => '01', 'Message' => 'Order not found'],
        });
    }

    /** Cổng giả lập để demo khi chưa có tài khoản sandbox */
    public function mock(string $code)
    {
        $tx = PaymentTransaction::with('order')->where('transaction_code', $code)->firstOrFail();

        return view('payment.mock', compact('tx'));
    }

    public function mockPay(Request $request, string $code)
    {
        $tx = PaymentTransaction::with('order')->where('transaction_code', $code)->firstOrFail();
        $this->payment->settle($code, (int) $tx->amount, $request->input('result') === 'success', 'MOCK'.time(), ['mock' => true]);

        return redirect()->route('order.done', $tx->order->code);
    }
}
