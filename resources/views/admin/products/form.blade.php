@extends('layouts.admin')
@section('title', $product->exists ? 'Sửa sản phẩm' : 'Thêm sản phẩm')
@section('content')
<h1 class="text-xl font-bold mb-4">{{ $product->exists ? 'Sửa' : 'Thêm' }} sản phẩm @if($product->exists)<span class="text-sm font-normal text-gray-500">— hoàn thiện {{ $product->completeness }}%</span>@endif</h1>
<form method="POST" enctype="multipart/form-data" x-data="{ cat: '{{ old('category_id', $product->category_id) }}' }"
      action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
      class="bg-white rounded-xl p-6 shadow-sm grid md:grid-cols-2 gap-4 text-sm max-w-4xl">
    @csrf @if($product->exists) @method('PUT') @endif

    <label class="md:col-span-2">Tên sản phẩm<input name="name" value="{{ old('name', $product->name) }}" class="w-full border rounded px-3 py-2 mt-1" required></label>
    <label>SKU<input name="sku" value="{{ old('sku', $product->sku) }}" class="w-full border rounded px-3 py-2 mt-1" required></label>
    <label>Danh mục
        <select name="category_id" x-model="cat" class="w-full border rounded px-3 py-2 mt-1" required><option value="">-- chọn --</option>
            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
    <label>Thương hiệu
        <select name="brand_id" class="w-full border rounded px-3 py-2 mt-1"><option value="">--</option>
            @foreach($brands as $b)<option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id) == $b->id)>{{ $b->name }}</option>@endforeach</select></label>
    <label>Trạng thái
        <select name="status" class="w-full border rounded px-3 py-2 mt-1">@foreach(\App\Models\Product::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', $product->status) == $k)>{{ $l }}</option>@endforeach</select></label>
    <label>Giá gốc (₫)<input type="number" name="price" value="{{ old('price', $product->price) }}" class="w-full border rounded px-3 py-2 mt-1" required></label>
    <label>Giá khuyến mãi (₫)<input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full border rounded px-3 py-2 mt-1"></label>
    <label>Tồn kho<input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" class="w-full border rounded px-3 py-2 mt-1" required></label>
    <label>Ảnh sản phẩm<input type="file" name="image" accept="image/*" class="w-full mt-1">@if($product->image)<img src="{{ $product->image_url }}" class="w-16 mt-1 rounded">@endif</label>
    <label class="md:col-span-2">Mô tả<textarea name="description" rows="3" class="w-full border rounded px-3 py-2 mt-1">{{ old('description', $product->description) }}</textarea></label>

    {{-- Thuộc tính động: hiện đúng bộ thuộc tính của danh mục đang chọn --}}
    <div class="md:col-span-2 border-t pt-4">
        <div class="font-semibold mb-2">Thuộc tính sản phẩm (theo danh mục)</div>
        <p x-show="!cat" class="text-gray-500">Chọn danh mục để hiện các thuộc tính.</p>
        @foreach($categories as $c)
        <div x-show="cat == '{{ $c->id }}'" x-cloak class="grid md:grid-cols-2 gap-3">
            @foreach($c->attributes as $a)
            <label>{{ $a->name }} @if($a->is_required)<span class="text-red-500">*</span>@endif
                @if($a->type === 'select')
                    <select name="attr[{{ $a->id }}]" :disabled="cat != '{{ $c->id }}'" class="w-full border rounded px-3 py-2 mt-1"><option value="">--</option>
                        @foreach($a->options ?? [] as $o)<option @selected(old("attr.{$a->id}", $values[$a->id] ?? '') == $o)>{{ $o }}</option>@endforeach</select>
                @else
                    <input type="{{ $a->type === 'number' ? 'number' : 'text' }}" step="any" name="attr[{{ $a->id }}]" :disabled="cat != '{{ $c->id }}'"
                           value="{{ old("attr.{$a->id}", $values[$a->id] ?? '') }}" class="w-full border rounded px-3 py-2 mt-1">
                @endif
            </label>
            @endforeach
        </div>
        @endforeach
    </div>
    <div class="md:col-span-2 flex gap-3"><button class="bg-indigo-600 text-white px-6 py-2 rounded-lg">Lưu</button><a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-gray-600">Quay lại</a></div>
</form>
<style>[x-cloak]{display:none!important}</style>
@endsection
