@extends('layouts.app')
@section('title', 'Thanh toán')
@section('content')
<h1 class="text-xl font-bold mb-4">Thông tin đặt hàng</h1>
<form method="POST" action="{{ route('checkout.place') }}" class="grid md:grid-cols-3 gap-6">@csrf
    <div class="md:col-span-2 bg-white rounded-xl p-6 shadow-sm space-y-3 text-sm">
        <input name="name" value="{{ old('name', auth()->user()?->name) }}" placeholder="Họ tên" class="w-full border rounded px-3 py-2" required>
        <input name="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="Số điện thoại" class="w-full border rounded px-3 py-2" required>
        <input name="address" value="{{ old('address') }}" placeholder="Địa chỉ nhận hàng" class="w-full border rounded px-3 py-2" required>
        <input name="note" value="{{ old('note') }}" placeholder="Ghi chú (tùy chọn)" class="w-full border rounded px-3 py-2">
        <div class="font-semibold pt-2">Phương thức thanh toán</div>
        <label class="flex items-center gap-2 border rounded p-3"><input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng (COD)</label>
        <label class="flex items-center gap-2 border rounded p-3"><input type="radio" name="payment_method" value="vnpay"> Thanh toán online qua VNPay{{ config('payment.vnpay.tmn_code') ? '' : ' (chế độ giả lập)' }}</label>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm text-sm h-fit space-y-2">
        <div class="font-semibold">Đơn hàng</div>
        @foreach($items as $i)<div class="flex justify-between"><span>{{ $i->product->name }} × {{ $i->quantity }}</span><span>{{ number_format($i->line_total, 0, ',', '.') }}₫</span></div>@endforeach
        <div class="flex justify-between border-t pt-2"><span>Vận chuyển</span><span>{{ $shipping ? number_format($shipping, 0, ',', '.').'₫' : 'Miễn phí' }}</span></div>
        <div class="flex justify-between font-bold text-lg"><span>Tổng</span><span class="text-red-600">{{ number_format($total, 0, ',', '.') }}₫</span></div>
        <button class="w-full bg-indigo-600 text-white rounded-lg py-2">Đặt hàng</button>
    </div>
</form>
@endsection
