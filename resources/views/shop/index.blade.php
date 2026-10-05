@extends('layouts.app')
@section('title', $category->name ?? 'Sản phẩm')
@section('content')
<div class="flex flex-col md:flex-row gap-6">
    <form method="GET" class="md:w-60 shrink-0 bg-white rounded-xl p-4 shadow-sm space-y-4 text-sm h-fit">
        <input type="hidden" name="q" value="{{ request('q') }}">
        <div>
            <div class="font-semibold mb-1">Danh mục</div>
            <a href="{{ route('products.index') }}" class="block {{ !$category ? 'text-indigo-600' : '' }}">Tất cả</a>
            @foreach($categories as $c)<a href="{{ route('category.show', $c->slug) }}" class="block {{ $category?->id === $c->id ? 'text-indigo-600 font-medium' : '' }}">{{ $c->name }}</a>@endforeach
        </div>
        <div>
            <div class="font-semibold mb-1">Thương hiệu</div>
            <select name="brand" class="w-full border rounded px-2 py-1"><option value="">Tất cả</option>
                @foreach($brands as $b)<option value="{{ $b->id }}" @selected(request('brand') == $b->id)>{{ $b->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <div class="font-semibold mb-1">Khoảng giá (₫)</div>
            <div class="flex gap-1"><input name="min" value="{{ request('min') }}" placeholder="Từ" class="w-1/2 border rounded px-2 py-1"><input name="max" value="{{ request('max') }}" placeholder="Đến" class="w-1/2 border rounded px-2 py-1"></div>
        </div>
        {{-- Bộ lọc thuộc tính động của danh mục (PIM) --}}
        @foreach($filters as $f)
            <div>
                <div class="font-semibold mb-1">{{ $f->name }}</div>
                <select name="attr[{{ $f->id }}]" class="w-full border rounded px-2 py-1"><option value="">Tất cả</option>
                    @foreach($f->choices as $opt)<option @selected(request("attr.{$f->id}") == $opt)>{{ $opt }}</option>@endforeach
                </select>
            </div>
        @endforeach
        <div>
            <div class="font-semibold mb-1">Sắp xếp</div>
            <select name="sort" class="w-full border rounded px-2 py-1">
                <option value="">Mới nhất</option>
                <option value="price_asc" @selected(request('sort')=='price_asc')>Giá tăng dần</option>
                <option value="price_desc" @selected(request('sort')=='price_desc')>Giá giảm dần</option>
            </select>
        </div>
        <button class="w-full bg-indigo-600 text-white rounded py-1.5">Lọc</button>
    </form>
    <div class="flex-1">
        <h1 class="text-xl font-bold mb-4">{{ $category->name ?? 'Tất cả sản phẩm' }} <span class="text-sm font-normal text-gray-500">({{ $products->total() }})</span></h1>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($products as $p) @include('shop._card', ['p' => $p]) @empty <p class="col-span-full text-gray-500">Không tìm thấy sản phẩm.</p> @endforelse
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</div>
@endsection
