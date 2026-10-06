@extends('layouts.admin')
@section('title', __('Đơn :code', ['code' => $order->code]))
@section('content')
<a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500">{{ __('← Danh sách đơn') }}</a>
<h1 class="text-xl font-bold my-3">{{ __('Đơn :code', ['code' => $order->code]) }}</h1>
<div class="grid lg:grid-cols-3 gap-6 text-sm">
    <div class="lg:col-span-2 bg-white rounded-xl p-4 shadow-sm">
        <div class="mb-3 text-gray-600">{{ $order->name }} · {{ $order->phone }}<br>{{ $order->address }}@if($order->note)<br><i>{{ __('Ghi chú:') }} {{ $order->note }}</i>@endif</div>
        <table class="w-full">
            @foreach($order->items as $i)<tr class="border-t"><td class="py-2">{{ __($i->name) }} <span class="text-gray-400">({{ $i->sku }})</span></td><td>× {{ $i->quantity }}</td><td class="text-right">{{ number_format($i->price * $i->quantity, 0, ',', '.') }}₫</td></tr>@endforeach
            <tr class="border-t"><td class="py-2" colspan="2">{{ __('Phí vận chuyển') }}</td><td class="text-right">{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</td></tr>
            <tr class="border-t font-bold"><td class="py-2" colspan="2">{{ __('Tổng') }}</td><td class="text-right">{{ number_format($order->total, 0, ',', '.') }}₫</td></tr>
        </table>
        <div class="font-semibold mt-5 mb-1">{{ __('Giao dịch thanh toán') }}</div>
        @foreach($order->transactions as $t)<div class="border-t py-1 flex justify-between"><span>{{ $t->transaction_code }} ({{ $t->gateway }})</span><span>{{ $t->status }} {{ $t->paid_at?->format('d/m H:i') }}</span></div>@endforeach
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm h-fit space-y-3">
        <div>{{ __('Trạng thái:') }} <b>{{ __(\App\Models\Order::STATUSES[$order->status]) }}</b></div>
        <div>{{ __('Thanh toán:') }} <b>{{ __(\App\Models\Order::PAYMENT_STATUSES[$order->payment_status]) }}</b></div>
        @foreach(\App\Models\Order::TRANSITIONS[$order->status] as $to)
        <form method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $to }}">
            <button class="w-full rounded py-2 text-white {{ $to === 'cancelled' ? 'bg-red-500' : 'bg-indigo-600' }}">→ {{ __(\App\Models\Order::STATUSES[$to]) }}</button></form>
        @endforeach
    </div>
</div>
@endsection
