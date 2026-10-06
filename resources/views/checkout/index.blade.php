@extends('layouts.app')
@section('title', __('Thanh toán'))
@section('content')
<p class="checkout-step">
<a href="{{ route('cart.index') }}">{{ __('Giỏ hàng') }}</a> &nbsp; / &nbsp; <strong>{{ __('Thông tin & thanh toán') }}</strong> &nbsp; / &nbsp; {{ __('Hoàn tất') }}</p>
<div class="page-heading">
<h1>{{ __('Hoàn tất đơn hàng') }}</h1>
<p>{{ __('Điền thông tin nhận hàng và chọn cách thanh toán phù hợp.') }}</p>
</div>
<form method="POST" action="{{ route('checkout.place') }}" class="checkout-layout">@csrf
<div class="checkout-fields">
<h2>{{ __('01. Thông tin nhận hàng') }}</h2>
<div class="form-grid">
<div class="form-field">
<label for="order-name">{{ __('Họ và tên *') }}</label>
<input id="order-name" class="field" name="name" autocomplete="name" value="{{ old('name',auth()->user()?->name) }}" required>
</div>
<div class="form-field">
<label for="order-phone">{{ __('Số điện thoại *') }}</label>
<input id="order-phone" class="field" name="phone" type="tel" autocomplete="tel" value="{{ old('phone',auth()->user()?->phone) }}" required>
</div>
<div class="form-field full">
<label for="order-address">{{ __('Địa chỉ nhận hàng *') }}</label>
<input id="order-address" class="field" name="address" autocomplete="street-address" value="{{ old('address') }}" placeholder="{{ __('Số nhà, đường, phường/xã, tỉnh/thành phố') }}" required>
</div>
<div class="form-field full">
<label for="order-note">{{ __('Ghi chú') }} <span style="font-weight:400;color:var(--muted)">{{ __('(tùy chọn)') }}</span>
</label>
<textarea id="order-note" class="field" name="note" rows="2" placeholder="{{ __('Điều gì người giao hàng cần biết?') }}">{{ old('note') }}</textarea>
</div>
</div>
<div class="payment-options">
<h2>{{ __('02. Phương thức thanh toán') }}</h2>
<label class="payment-option">
<input type="radio" name="payment_method" value="cod" @checked(old('payment_method','cod')==='cod')>
<span>
<strong>{{ __('Thanh toán khi nhận hàng') }}</strong>
<small>{{ __('Trả tiền cho người giao hàng (COD)') }}</small>
</span>
</label>
<label class="payment-option">
<input type="radio" name="payment_method" value="vnpay" @checked(old('payment_method')==='vnpay')>
<span>
<strong>{{ __('Thanh toán qua VNPay') }}</strong>
<small>{{ config('payment.vnpay.tmn_code') ? __('Chuyển đến cổng thanh toán sau khi đặt hàng') : __('Cổng giả lập để trải nghiệm luồng thanh toán') }}</small>
</span>
</label>
</div>
</div>
<aside class="order-summary">
<h2>{{ __('Đơn hàng của bạn') }}</h2>@foreach($items as $i)<div class="summary-product">
<span>{{ __($i->product->name) }}<br>
<small>{{ __('Số lượng: :count', ['count' => $i->quantity]) }}</small>
</span>
<span>{{ number_format($i->line_total,0,',','.') }}₫</span>
</div>@endforeach<div class="summary-row" style="margin-top:20px">
<span>{{ __('Vận chuyển') }}</span>
<span>{{ $shipping ? number_format($shipping,0,',','.').'₫' : __('Miễn phí') }}</span>
</div>
<div class="summary-row summary-total">
<span>{{ __('Tổng cộng') }}</span>
<span>{{ number_format($total,0,',','.') }}₫</span>
</div>
<button class="button button-primary">{{ __('Đặt hàng') }} @include('components.icon',['name'=>'arrow','size'=>18])</button>
<p class="summary-note">{{ __('Kiểm tra địa chỉ và số điện thoại trước khi đặt hàng.') }}</p>
<a href="{{ route('cart.index') }}" class="filter-reset">{{ __('Quay lại chỉnh sửa giỏ hàng') }}</a>
</aside>
</form>
@endsection
