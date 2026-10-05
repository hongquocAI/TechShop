<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::latest();
        if ($s = $request->query('status')) $q->where('status', $s);
        if ($c = $request->query('q')) $q->where(fn ($w) => $w->where('code', 'like', "%$c%")->orWhere('phone', 'like', "%$c%"));

        return view('admin.orders.index', ['orders' => $q->paginate(15)->withQueryString()]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load(['items', 'transactions'])]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $to = $request->validate(['status' => 'required|in:'.implode(',', array_keys(Order::STATUSES))])['status'];
        if (! $order->canTransitionTo($to)) {
            return back()->with('error', 'Không thể chuyển trạng thái này.');
        }
        $to === 'cancelled' ? $order->cancel() : $order->update(['status' => $to]);
        // COD: hoàn thành đơn = đã thu tiền
        if ($to === 'completed' && $order->payment_method === 'cod') {
            $order->update(['payment_status' => 'paid']);
        }

        return back()->with('success', 'Đã cập nhật trạng thái.');
    }
}
