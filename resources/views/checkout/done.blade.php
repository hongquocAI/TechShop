@extends('layouts.app')
@section('title', __('Đơn hàng :code', ['code' => $order->code]))
@section('content')
<div class="order-result">
<div class="order-result-header">
<span class="result-icon">@include('components.icon',['name'=>'check','size'=>26])</span>
<p class="eyebrow">{{ __('THÔNG TIN ĐƠN HÀNG') }}</p>
<h1>{{ $order->status === 'cancelled' ? __('Đơn hàng đã hủy') : __('Đã nhận đơn hàng của bạn') }}</h1>
<p>{{ __('Mã đơn') }} <strong>{{ $order->code }}</strong>
</p>
<div class="order-states">
<span class="status-chip">{{ __(\App\Models\Order::STATUSES[$order->status]) }}</span>
<span class="status-chip">{{ __(\App\Models\Order::PAYMENT_STATUSES[$order->payment_status]) }}</span>
</div>
</div>
<div class="order-result-body">
<div class="delivery-info">
<h2>{{ __('Thông tin nhận hàng') }}</h2>
<p>
<strong>{{ $order->name }}</strong> · {{ $order->phone }}<br>{{ $order->address }}</p>
<p>{{ __('Phương thức: :method', ['method' => strtoupper($order->payment_method)]) }}</p>
</div>
<table class="order-table">
<thead>
<tr>
<th>{{ __('Sản phẩm') }}</th>
<th>{{ __('Thành tiền') }}</th>
</tr>
</thead>
<tbody>@foreach($order->items as $i)<tr>
<td>{{ __($i->name) }} <small>× {{ $i->quantity }}</small>
</td>
<td>{{ number_format($i->price*$i->quantity,0,',','.') }}₫</td>
</tr>@endforeach<tr>
<td>{{ __('Phí vận chuyển') }}</td>
<td>{{ $order->shipping_fee ? number_format($order->shipping_fee,0,',','.').'₫' : __('Miễn phí') }}</td>
</tr>
<tr class="order-total">
<td>{{ __('Tổng cộng') }}</td>
<td>{{ number_format($order->total,0,',','.') }}₫</td>
</tr>
</tbody>
</table>
<a href="{{ route('products.index') }}" class="button button-primary">{{ __('Tiếp tục mua sắm') }} @include('components.icon',['name'=>'arrow','size'=>18])</a>
</div>
</div>
@endsection
