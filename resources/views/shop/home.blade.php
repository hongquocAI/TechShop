@extends('layouts.app')
@section('content')
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl p-8 mb-8">
    <h1 class="text-3xl font-bold">Phụ kiện công nghệ chính hãng</h1>
    <p class="mt-2 text-indigo-100">Tai nghe, sạc, cáp, pin dự phòng, chuột & bàn phím — miễn phí vận chuyển từ {{ number_format(config('payment.free_ship_from'), 0, ',', '.') }}₫</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
    @foreach($categories as $c)
        <a href="{{ route('category.show', $c->slug) }}" class="bg-white rounded-xl p-4 text-center shadow-sm hover:shadow-md">
            <div class="font-semibold">{{ $c->name }}</div><div class="text-xs text-gray-500">{{ $c->products_count }} sản phẩm</div>
        </a>
    @endforeach
</div>
<h2 class="text-xl font-bold mb-4">Sản phẩm mới</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($featured as $p) @include('shop._card', ['p' => $p]) @endforeach
</div>
@endsection
