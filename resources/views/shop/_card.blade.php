<article class="product-card">
<a href="{{ route('products.show', $p->slug) }}" class="product-image" aria-label="{{ __('Xem :name', ['name' => __($p->name)]) }}">@include('components.product-image', ['product' => $p])@if($p->final_price < $p->price)<span class="sale-tag">−{{ round((1 - $p->final_price / $p->price) * 100) }}%</span>@endif</a>
<div class="product-info">
<p class="product-brand">{{ $p->brand?->name ?? 'TechShop' }}</p>
<h3>
<a href="{{ route('products.show', $p->slug) }}">{{ __($p->name) }}</a>
</h3>
<div class="product-prices">
<strong>{{ number_format($p->final_price, 0, ',', '.') }}₫</strong>@if($p->final_price < $p->price)<del>{{ number_format($p->price, 0, ',', '.') }}₫</del>@endif</div>
<div class="product-card-bottom">
<span class="stock-label {{ $p->stock <= 0 ? 'sold-out' : '' }}">{{ $p->stock > 0 ? __('Còn hàng') : __('Hết hàng') }}</span>@if($p->stock > 0)<form method="POST" action="{{ route('cart.add', $p) }}">@csrf<input type="hidden" name="quantity" value="1">
<button type="submit" class="quick-add" aria-label="{{ __('Thêm :name vào giỏ', ['name' => __($p->name)]) }}">@include('components.icon', ['name' => 'plus', 'size' => 18])</button>
</form>@endif</div>
</div>
</article>
