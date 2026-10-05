@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="text-sm text-gray-500 mb-4"><a href="{{ route('home') }}">Trang chủ</a> / <a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}</a> / {{ $product->name }}</div>
<div class="bg-white rounded-xl p-6 shadow-sm grid md:grid-cols-2 gap-8">
    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full rounded-lg">
    <div>
        <div class="text-sm text-gray-500">{{ $product->brand?->name }} · SKU {{ $product->sku }}</div>
        <h1 class="text-2xl font-bold mt-1">{{ $product->name }}</h1>
        <div class="mt-3 flex items-baseline gap-3">
            <span class="text-3xl text-red-600 font-bold">{{ number_format($product->final_price, 0, ',', '.') }}₫</span>
            @if($product->final_price < $product->price)<span class="text-gray-400 line-through">{{ number_format($product->price, 0, ',', '.') }}₫</span>@endif
        </div>
        <p class="mt-4 text-gray-600">{{ $product->description }}</p>
        <div class="mt-4 text-sm {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $product->stock > 0 ? "Còn {$product->stock} sản phẩm" : 'Hết hàng' }}</div>
        @if($product->stock > 0)
        <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-4 flex gap-3">@csrf
            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 border rounded px-2 py-2">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">Thêm vào giỏ</button>
        </form>
        @endif
        <h2 class="font-semibold mt-8 mb-2">Thông số kỹ thuật</h2>
        <table class="w-full text-sm">
            @forelse($product->attributeValues as $v)
                <tr class="border-t"><td class="py-2 text-gray-500 w-1/2">{{ $v->attribute->name }}</td><td>{{ $v->value }}</td></tr>
            @empty <tr><td class="text-gray-500">Chưa có thông số.</td></tr> @endforelse
        </table>
    </div>
</div>
@if($related->count())
<h2 class="text-xl font-bold mt-8 mb-4">Sản phẩm liên quan</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">@foreach($related as $p) @include('shop._card', ['p' => $p]) @endforeach</div>
@endif
@endsection
