@extends('layouts.app')
@section('title', __('Giỏ hàng'))
@section('content')
<nav class="breadcrumbs" aria-label="{{ __('Đường dẫn') }}">
<a href="{{ route('home') }}">{{ __('Trang chủ') }}</a>
<span>/</span>
<span>{{ __('Giỏ hàng') }}</span>
</nav>
<div class="page-heading">
<p class="eyebrow">{{ __('NHỮNG MÓN BẠN ĐÃ CHỌN') }}</p>
<h1>{{ __('Giỏ hàng của bạn') }}</h1>
<p>{{ __('Kiểm tra số lượng trước khi tiếp tục đặt hàng.') }}</p>
</div>
@if($items->isEmpty())<div class="empty-state">@include('components.icon',['name'=>'bag','size'=>42])<h2>{{ __('Giỏ hàng đang chờ món đầu tiên') }}</h2>
<p>{{ __('Khám phá tai nghe, sạc và những phụ kiện cho mỗi ngày.') }}</p>
<a class="button button-primary" href="{{ route('products.index') }}">{{ __('Khám phá sản phẩm') }} @include('components.icon',['name'=>'arrow','size'=>18])</a>
</div>
@else
<div class="cart-layout">
<div>@if($subtotal >= config('payment.free_ship_from'))<div class="free-shipping-note">{{ __('Đơn hàng của bạn được miễn phí vận chuyển.') }}</div>@else<div class="free-shipping-note">{{ __('Thêm :amount₫ để được miễn phí vận chuyển.', ['amount' => number_format(config('payment.free_ship_from') - $subtotal, 0, ',', '.')]) }}</div>@endif
<div class="cart-items">@foreach($items as $i)<article class="cart-item">
<a class="cart-item-image" href="{{ route('products.show',$i->product->slug) }}">@include('components.product-image',['product'=>$i->product])</a>
<div>
<h2>
<a href="{{ route('products.show',$i->product->slug) }}">{{ __($i->product->name) }}</a>
</h2>
<p class="cart-item-price">{{ number_format($i->product->final_price,0,',','.') }}₫ {{ __('/ sản phẩm') }}</p>
<div class="cart-item-controls">
<form method="POST" action="{{ route('cart.update',$i->product) }}" class="flex items-center gap-3">@csrf @method('PATCH')<div class="quantity-control" data-quantity>
<button type="button" data-step="-1" aria-label="{{ __('Giảm số lượng :name', ['name' => __($i->product->name)]) }}">@include('components.icon',['name'=>'minus','size'=>13])</button>
<input type="number" name="quantity" aria-label="{{ __('Số lượng :name', ['name' => __($i->product->name)]) }}" value="{{ $i->quantity }}" min="1" max="{{ $i->product->stock }}" required>
<button type="button" data-step="1" aria-label="{{ __('Tăng số lượng :name', ['name' => __($i->product->name)]) }}">@include('components.icon',['name'=>'plus','size'=>13])</button>
</div>
<button class="link-button">{{ __('Cập nhật') }}</button>
</form>
<form method="POST" action="{{ route('cart.remove',$i->product) }}">@csrf @method('DELETE')<button class="link-button remove-button" aria-label="{{ __('Xóa :name khỏi giỏ', ['name' => __($i->product->name)]) }}">{{ __('Xóa') }}</button>
</form>
</div>
</div>
<strong class="cart-item-total">{{ number_format($i->line_total,0,',','.') }}₫</strong>
</article>@endforeach</div>
<a class="text-link" href="{{ route('products.index') }}" style="margin-top:20px">{{ __('← Tiếp tục mua sắm') }}</a>
</div>
<aside class="order-summary">
<h2>{{ __('Tóm tắt giỏ hàng') }}</h2>
<div class="summary-row">
<span>{{ __('Tạm tính') }}</span>
<span>{{ number_format($subtotal,0,',','.') }}₫</span>
</div>
<div class="summary-row">
<span>{{ __('Phí vận chuyển') }}</span>
<span>{{ $shipping ? number_format($shipping,0,',','.').'₫' : __('Miễn phí') }}</span>
</div>
<div class="summary-row summary-total">
<span>{{ __('Tổng cộng') }}</span>
<span>{{ number_format($total,0,',','.') }}₫</span>
</div>
<a href="{{ route('checkout.form') }}" class="button button-primary">{{ __('Tiếp tục đặt hàng') }} @include('components.icon',['name'=>'arrow','size'=>18])</a>
<p class="summary-note">{{ __('Bạn có thể đặt hàng không cần tài khoản. Chọn COD hoặc VNPay ở bước tiếp theo.') }}</p>
</aside>
</div>
@endif
@endsection
