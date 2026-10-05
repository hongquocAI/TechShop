<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function orders(Request $request)
    {
        return view('account.orders', ['orders' => $request->user()->orders()->latest()->paginate(10)]);
    }

    public function cancel(Request $request, string $code)
    {
        $order = $request->user()->orders()->where('code', $code)->firstOrFail();
        if (! $order->canTransitionTo('cancelled') || $order->payment_status === 'paid') {
            return back()->with('error', 'Đơn này không thể tự hủy, vui lòng liên hệ cửa hàng.');
        }
        $order->cancel();

        return back()->with('success', 'Đã hủy đơn hàng.');
    }
}
