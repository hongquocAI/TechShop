@extends('layouts.app')
@section('title', __($product->name))
@section('content')
<nav class="breadcrumbs" aria-label="{{ __('Đường dẫn') }}">
<a href="{{ route('home') }}">{{ __('Trang chủ') }}</a>
<span>/</span>
<a href="{{ route('category.show', $product->category->slug) }}">{{ __($product->category->name) }}</a>
<span>/</span>
<span>{{ __($product->name) }}</span>
</nav>
<div class="detail-grid">
<div class="detail-image">@include('components.product-image', ['product'=>$product,'loading'=>'eager'])</div>
<div class="detail-info">
<p class="product-brand">{{ $product->brand?->name }} · SKU {{ $product->sku }}</p>
<h1>{{ __($product->name) }}</h1>
<div class="product-prices">
<strong>{{ number_format($product->final_price, 0, ',', '.') }}₫</strong>@if($product->final_price < $product->price)<del>{{ number_format($product->price, 0, ',', '.') }}₫</del>
<span class="sale-tag" style="position:static">{{ __('Tiết kiệm :amount₫', ['amount' => number_format($product->price - $product->final_price, 0, ',', '.')]) }}</span>@endif</div>
<span class="stock-label {{ $product->stock <= 0 ? 'sold-out' : '' }}">{{ $product->stock > 0 ? __('Còn hàng · :count sản phẩm', ['count' => $product->stock]) : __('Tạm hết hàng') }}</span>
<p class="detail-description">{{ __($product->description) }}</p>
@if($product->stock > 0)<form method="POST" action="{{ route('cart.add', $product) }}" class="add-to-cart">@csrf<label class="sr-only" for="product-quantity">{{ __('Số lượng') }}</label>
<div class="quantity-control" data-quantity>
<button type="button" data-step="-1" aria-label="{{ __('Giảm số lượng') }}">@include('components.icon',['name'=>'minus','size'=>15])</button>
<input id="product-quantity" type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" required>
<button type="button" data-step="1" aria-label="{{ __('Tăng số lượng') }}">@include('components.icon',['name'=>'plus','size'=>15])</button>
</div>
<button class="button button-primary">@include('components.icon',['name'=>'bag','size'=>18]) {{ __('Thêm vào giỏ hàng') }}</button>
</form>@endif
<div class="detail-perks">
<span>@include('components.icon',['name'=>'truck','size'=>17]) {{ __('Miễn phí giao từ :amount₫', ['amount' => number_format(config('payment.free_ship_from'), 0, ',', '.')]) }}</span>
<span>@include('components.icon',['name'=>'shield','size'=>17]) {{ __('Thanh toán COD hoặc VNPay') }}</span>
</div>
<div class="specifications">
<h2>{{ __('Thông số kỹ thuật') }}</h2>
<table>
<tbody>@forelse($product->attributeValues as $v)<tr>
<td>{{ __($v->attribute->name) }}</td>
<td>{{ __($v->value) }}</td>
</tr>@empty<tr>
<td colspan="2">{{ __('Thông số đang được cập nhật.') }}</td>
</tr>@endforelse</tbody>
</table>
</div>
</div>
</div>
@if($related->count())<section class="related-section">
<div class="section-heading">
<div>
<p class="eyebrow">{{ __('THÊM MỘT VÀI LỰA CHỌN') }}</p>
<h2>{{ __('Cùng danh mục') }}</h2>
</div>
<a href="{{ route('category.show',$product->category->slug) }}" class="text-link">{{ __('Xem tất cả') }} @include('components.icon',['name'=>'arrow','size'=>18])</a>
</div>
<div class="product-grid">@foreach($related as $p) @include('shop._card', ['p' => $p]) @endforeach</div>
</section>@endif
@endsection
