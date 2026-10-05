@extends('layouts.admin')
@section('title', 'Sản phẩm')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Sản phẩm</h1>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ Thêm sản phẩm</a>
</div>
<form class="flex gap-2 mb-4 text-sm"><input name="q" value="{{ request('q') }}" placeholder="Tên hoặc SKU" class="border rounded px-3 py-1.5">
    <select name="status" class="border rounded px-2"><option value="">Mọi trạng thái</option>@foreach(\App\Models\Product::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status')==$k)>{{ $l }}</option>@endforeach</select>
    <button class="bg-gray-800 text-white px-4 rounded">Lọc</button></form>
<table class="w-full bg-white rounded-xl shadow-sm text-sm">
    <tr class="text-left text-gray-500"><th class="p-3">Sản phẩm</th><th>SKU</th><th>Danh mục</th><th>Giá</th><th>Tồn</th><th>Hoàn thiện</th><th>Trạng thái</th><th></th></tr>
    @foreach($products as $p)
    <tr class="border-t">
        <td class="p-3 flex items-center gap-2"><img src="{{ $p->image_url }}" class="w-8 h-8 rounded object-cover">{{ $p->name }}</td>
        <td>{{ $p->sku }}</td><td>{{ $p->category->name }}</td><td>{{ number_format($p->final_price, 0, ',', '.') }}₫</td>
        <td class="{{ $p->stock <= 5 ? 'text-red-600 font-semibold' : '' }}">{{ $p->stock }}</td>
        <td><div class="w-24 bg-gray-200 rounded h-2"><div class="h-2 rounded {{ $p->completeness >= 100 ? 'bg-green-500' : 'bg-yellow-500' }}" style="width: {{ $p->completeness }}%"></div></div><span class="text-xs">{{ $p->completeness }}%</span></td>
        <td>{{ \App\Models\Product::STATUSES[$p->status] }}</td>
        <td class="space-x-2"><a href="{{ route('admin.products.edit', $p) }}" class="text-indigo-600">Sửa</a>
            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline" onsubmit="return confirm('Xóa sản phẩm?')">@csrf @method('DELETE')<button class="text-red-500">Xóa</button></form></td>
    </tr>
    @endforeach
</table>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
