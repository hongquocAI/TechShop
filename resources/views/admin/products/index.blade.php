@extends('layouts.admin')
@section('title', __('Sản phẩm'))
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">{{ __('Sản phẩm') }}</h1>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">{{ __('+ Thêm sản phẩm') }}</a>
</div>
<form class="flex gap-2 mb-4 text-sm"><input name="q" value="{{ request('q') }}" placeholder="{{ __('Tên hoặc SKU') }}" class="border rounded px-3 py-1.5">
    <select name="status" class="border rounded px-2"><option value="">{{ __('Mọi trạng thái') }}</option>@foreach(\App\Models\Product::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status')==$k)>{{ __($l) }}</option>@endforeach</select>
    <button class="bg-gray-800 text-white px-4 rounded">{{ __('Lọc') }}</button></form>
<table class="w-full bg-white rounded-xl shadow-sm text-sm">
    <tr class="text-left text-gray-500"><th class="p-3">{{ __('Sản phẩm') }}</th><th>SKU</th><th>{{ __('Danh mục') }}</th><th>{{ __('Giá') }}</th><th>{{ __('Tồn') }}</th><th>{{ __('Hoàn thiện') }}</th><th>{{ __('Trạng thái') }}</th><th></th></tr>
    @foreach($products as $p)
    <tr class="border-t">
        <td class="p-3 flex items-center gap-2"><img src="{{ $p->image_url }}" class="w-8 h-8 rounded object-cover">{{ __($p->name) }}</td>
        <td>{{ $p->sku }}</td><td>{{ __($p->category->name) }}</td><td>{{ number_format($p->final_price, 0, ',', '.') }}₫</td>
        <td class="{{ $p->stock <= 5 ? 'text-red-600 font-semibold' : '' }}">{{ $p->stock }}</td>
        <td><div class="w-24 bg-gray-200 rounded h-2"><div class="h-2 rounded {{ $p->completeness >= 100 ? 'bg-green-500' : 'bg-yellow-500' }}" style="width: {{ $p->completeness }}%"></div></div><span class="text-xs">{{ $p->completeness }}%</span></td>
        <td>{{ __(\App\Models\Product::STATUSES[$p->status]) }}</td>
        <td class="space-x-2"><a href="{{ route('admin.products.edit', $p) }}" class="text-indigo-600">{{ __('Sửa') }}</a>
            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline" data-confirm="{{ __('Xóa sản phẩm?') }}">@csrf @method('DELETE')<button class="text-red-500">{{ __('Xóa') }}</button></form></td>
    </tr>
    @endforeach
</table>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
