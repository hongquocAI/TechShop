@if($product->image)
<img src="{{ $product->image_url }}" alt="{{ __($product->name) }}" loading="{{ $loading ?? 'lazy' }}" class="product-photo">
@else
@php
$device = match (true) {
str_contains($product->name, 'Bowie') => 'earbuds',
str_contains($product->name, 'Earphones') => 'wired-earphones',
str_contains($product->name, 'Cáp') => 'cable',
str_contains($product->name, 'Củ sạc') => 'charger',
str_contains($product->name, 'Chuột') => 'mouse',
str_contains($product->name, 'Bàn phím') => 'keyboard',
str_contains($product->name, 'Pin') => 'powerbank',
default => 'headphones',
};
@endphp
@include('components.device', ['device' => $device, 'label' => __('Hình minh họa :name', ['name' => __($product->name)])])
<span class="illustration-note">{{ __('Ảnh minh họa') }}</span>
@endif

