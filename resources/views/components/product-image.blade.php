@if($product->image)
<img src="{{ $product->image_url }}" alt="{{ __($product->name) }}" loading="{{ $loading ?? 'lazy' }}" class="product-photo">
@else
@php
$device = match ($product->category?->slug) {
'tai-nghe' => str_contains($product->name, 'Bowie') ? 'earbuds' : (str_contains($product->name, 'Earphones') ? 'wired-earphones' : 'headphones'),
'sac-cap' => str_contains($product->name, 'Cáp') ? 'cable' : 'charger',
'chuot-ban-phim' => str_contains($product->name, 'Bàn phím') ? 'keyboard' : 'mouse',
'pin-du-phong' => 'powerbank',
default => null,
};
@endphp
@if($device)
@include('components.device', ['device' => $device, 'label' => __('Hình minh họa :name', ['name' => __($product->name)])])
<span class="illustration-note">{{ __('Ảnh minh họa') }}</span>
@else
<span class="product-image-placeholder">@include('components.icon', ['name'=>'image','size'=>48])<span>{{ __('Chưa có ảnh') }}</span></span>
@endif
@endif

