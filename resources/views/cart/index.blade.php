@extends('layouts.app')
@section('title', 'Giỏ hàng')
@section('content')
<h1 class="text-xl font-bold mb-4">Giỏ hàng</h1>
@if($items->isEmpty())
    <p class="text-gray-500">Giỏ hàng trống. <a class="text-indigo-600" href="{{ route('products.index') }}">Tiếp tục mua sắm</a></p>
@else
<div class="bg-white rounded-xl shadow-sm divide-y">
    @foreach($items as $i)
    <div class="p-4 flex items-center gap-4">
        <img src="{{ $i->product->image_url }}" class="w-16 h-16 rounded object-cover">
        <div class="flex-1"><a href="{{ route('products.show', $i->product->slug) }}" class="font-medium">{{ $i->product->name }}</a>
            <div class="text-sm text-gray-500">{{ number_format($i->product->final_price, 0, ',', '.') }}₫</div></div>
        <form method="POST" action="{{ route('cart.update', $i->product) }}" class="flex gap-1">@csrf @method('PATCH')
            <input type="number" name="quantity" value="{{ $i->quantity }}" min="0" max="{{ $i->product->stock }}" class="w-16 border rounded px-2 py-1">
            <button class="text-sm text-indigo-600">Cập nhật</button></form>
        <div class="w-28 text-right font-semibold">{{ number_format($i->line_total, 0, ',', '.') }}₫</div>
        <form method="POST" action="{{ route('cart.remove', $i->product) }}">@csrf @method('DELETE')<button class="text-red-500 text-sm">Xóa</button></form>
    </div>
    @endforeach
</div>
<div class="mt-4 bg-white rounded-xl p-4 shadow-sm ml-auto max-w-sm space-y-1 text-sm">
    <div class="flex justify-between"><span>Tạm tính</span><span>{{ number_format($subtotal, 0, ',', '.') }}₫</span></div>
    <div class="flex justify-between"><span>Phí vận chuyển</span><span>{{ $shipping ? number_format($shipping, 0, ',', '.').'₫' : 'Miễn phí' }}</span></div>
    <div class="flex justify-between font-bold text-lg border-t pt-2"><span>Tổng</span><span class="text-red-600">{{ number_format($total, 0, ',', '.') }}₫</span></div>
    <a href="{{ route('checkout.form') }}" class="block text-center bg-indigo-600 text-white rounded-lg py-2 mt-2">Tiến hành đặt hàng</a>
</div>
@endif
@endsection
