@extends('layouts.app')
@section('title', 'Đơn hàng '.$order->code)
@section('content')
<div class="bg-white rounded-xl p-6 shadow-sm max-w-2xl mx-auto">
    <h1 class="text-xl font-bold">Đơn hàng {{ $order->code }}</h1>
    <div class="mt-2 flex gap-2 text-sm">
        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800">{{ \App\Models\Order::STATUSES[$order->status] }}</span>
        <span class="px-2 py-0.5 rounded {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] }}</span>
        <span class="px-2 py-0.5 rounded bg-gray-100">{{ strtoupper($order->payment_method) }}</span>
    </div>
    <p class="text-sm text-gray-600 mt-3">{{ $order->name }} · {{ $order->phone }}<br>{{ $order->address }}</p>
    <table class="w-full text-sm mt-4">
        @foreach($order->items as $i)<tr class="border-t"><td class="py-2">{{ $i->name }} × {{ $i->quantity }}</td><td class="text-right">{{ number_format($i->price * $i->quantity, 0, ',', '.') }}₫</td></tr>@endforeach
        <tr class="border-t"><td class="py-2">Phí vận chuyển</td><td class="text-right">{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</td></tr>
        <tr class="border-t font-bold"><td class="py-2">Tổng cộng</td><td class="text-right text-red-600">{{ number_format($order->total, 0, ',', '.') }}₫</td></tr>
    </table>
    <a href="{{ route('home') }}" class="inline-block mt-4 text-indigo-600">← Tiếp tục mua sắm</a>
</div>
@endsection
