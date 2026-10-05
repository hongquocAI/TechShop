<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $paid = Order::where('payment_status', 'paid')->where('status', '!=', 'cancelled');

        return view('admin.dashboard', [
            'revenue' => (clone $paid)->sum('total'),
            'orderCount' => Order::count(),
            'productCount' => Product::count(),
            'avgCompleteness' => (int) Product::avg('completeness'),
            'byStatus' => Order::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'lowStock' => Product::where('stock', '<=', 5)->orderBy('stock')->take(8)->get(),
            'incomplete' => Product::where('completeness', '<', 100)->orderBy('completeness')->take(8)->get(),
            'topProducts' => \App\Models\OrderItem::selectRaw('name, sum(quantity) qty')->groupBy('name')->orderByDesc('qty')->take(5)->get(),
            'gateways' => PaymentTransaction::selectRaw("gateway, count(*) total, sum(case when status='success' then 1 else 0 end) ok")->groupBy('gateway')->get(),
        ]);
    }
}
