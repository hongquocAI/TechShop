@extends('layouts.admin')
@section('title', 'Danh mục & Thuộc tính')
@section('content')
<h1 class="text-xl font-bold mb-4">Danh mục, Thương hiệu & Thuộc tính</h1>
<div class="grid lg:grid-cols-3 gap-6 text-sm">
    <div class="lg:col-span-2 space-y-4">
        @foreach($categories as $c)
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex justify-between"><div class="font-semibold">{{ $c->name }} <span class="text-gray-400 font-normal">({{ $c->products_count }} sp)</span></div>
                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Xóa danh mục?')">@csrf @method('DELETE')<button class="text-red-500">Xóa</button></form></div>
            <table class="w-full mt-2">
                @foreach($c->attributes as $a)
                <tr class="border-t"><td class="py-1">{{ $a->name }}</td><td class="text-gray-500">{{ $a->type }}{{ $a->options ? ': '.implode(', ', $a->options) : '' }}</td>
                    <td>{{ $a->is_required ? 'Bắt buộc' : '' }}</td><td>{{ $a->is_filterable ? 'Lọc' : '' }}</td>
                    <td class="text-right"><form method="POST" action="{{ route('admin.attributes.destroy', $a) }}">@csrf @method('DELETE')<button class="text-red-500">Xóa</button></form></td></tr>
                @endforeach
            </table>
            <form method="POST" action="{{ route('admin.attributes.store') }}" class="flex flex-wrap gap-2 mt-3 pt-3 border-t">@csrf
                <input type="hidden" name="category_id" value="{{ $c->id }}">
                <input name="name" placeholder="Tên thuộc tính" class="border rounded px-2 py-1" required>
                <select name="type" class="border rounded px-2 py-1"><option>text</option><option>number</option><option>select</option></select>
                <input name="options" placeholder="Lựa chọn (a, b, c)" class="border rounded px-2 py-1">
                <label><input type="checkbox" name="is_required" value="1"> Bắt buộc</label>
                <label><input type="checkbox" name="is_filterable" value="1"> Dùng để lọc</label>
                <button class="bg-gray-800 text-white px-3 rounded">+ Thêm</button>
            </form>
        </div>
        @endforeach
    </div>
    <div class="space-y-4">
        <div class="bg-white rounded-xl p-4 shadow-sm"><div class="font-semibold mb-2">Thêm danh mục</div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-2">@csrf<input name="name" class="border rounded px-2 py-1 flex-1" required><button class="bg-indigo-600 text-white px-3 rounded">Thêm</button></form></div>
        <div class="bg-white rounded-xl p-4 shadow-sm"><div class="font-semibold mb-2">Thương hiệu</div>
            @foreach($brands as $b)<div class="flex justify-between border-t py-1"><span>{{ $b->name }} <span class="text-gray-400">({{ $b->products_count }})</span></span>
                <form method="POST" action="{{ route('admin.brands.destroy', $b) }}">@csrf @method('DELETE')<button class="text-red-500">Xóa</button></form></div>@endforeach
            <form method="POST" action="{{ route('admin.brands.store') }}" class="flex gap-2 mt-3">@csrf<input name="name" class="border rounded px-2 py-1 flex-1" required><button class="bg-indigo-600 text-white px-3 rounded">Thêm</button></form></div>
    </div>
</div>
@endsection
