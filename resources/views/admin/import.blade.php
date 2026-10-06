@extends('layouts.admin')
@section('title', __('Nhập / Xuất CSV'))
@section('content')
<h1 class="text-xl font-bold mb-4">{{ __('Nhập / Xuất sản phẩm hàng loạt') }}</h1>
<div class="grid md:grid-cols-2 gap-6 text-sm">
    <div class="bg-white rounded-xl p-5 shadow-sm"><div class="font-semibold mb-2">{{ __('Xuất CSV') }}</div>
        <p class="text-gray-500 mb-3">{{ __('Tải toàn bộ sản phẩm (kèm thuộc tính) ra file CSV, mở được bằng Excel.') }}</p>
        <a href="{{ route('admin.export') }}" class="bg-gray-800 text-white px-4 py-2 rounded-lg inline-block">{{ __('Tải products.csv') }}</a></div>
    <div class="bg-white rounded-xl p-5 shadow-sm"><div class="font-semibold mb-2">{{ __('Nhập CSV') }}</div>
        <p class="text-gray-500 mb-3">{{ __('Các cột:') }} <code>sku, name, category, brand, price, stock, status, description, attributes</code>.<br>
            {{ __('Cột') }} <code>attributes</code> {{ __('dạng') }} <code>ma_thuoc_tinh=gia_tri;ma_khac=gia_tri</code>{{ __('. SKU đã có sẽ được cập nhật.') }}</p>
        <form method="POST" action="{{ route('admin.import.run') }}" enctype="multipart/form-data" class="flex gap-2">@csrf
            <input type="file" name="file" accept=".csv,.txt" required><button class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg">{{ __('Nhập') }}</button></form>
        @if(session('import_errors'))<ul class="mt-3 text-red-600 list-disc pl-5">@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>@endif</div>
</div>
@endsection
