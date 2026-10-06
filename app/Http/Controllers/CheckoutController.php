<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart, private OrderService $orders, private PaymentService $payment) {}

    public function form()
    {
        $summary = $this->cart->summary();
        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', $summary);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cod,vnpay',
        ], ['phone.regex' => __('Số điện thoại không hợp lệ.')]);

        try {
            $order = $this->orders->place($data, $request->user()?->id);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
        $this->cart->clear();

        if ($order->payment_method === 'vnpay') {
            $tx = $order->transactions()->first();

            // Chưa cấu hình VNPay -> dùng cổng giả lập để demo
            if (! config('payment.vnpay.tmn_code')) {
                return redirect()->route('payment.mock', $tx->transaction_code);
            }

            return redirect()->away($this->payment->vnpayUrl($tx, $request->ip()));
        }

        return redirect()->route('order.done', $order->code);
    }

    public function done(string $code)
    {
        $order = \App\Models\Order::with('items')->where('code', $code)->firstOrFail();

        return view('checkout.done', compact('order'));
    }
}
