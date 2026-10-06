@extends('layouts.admin')
@section('title', __('Đơn hàng'))
@section('content')
<h1 class="text-xl font-bold mb-4">{{ __('Đơn hàng') }}</h1>
<form class="flex gap-2 mb-4 text-sm"><input name="q" value="{{ request('q') }}" placeholder="{{ __('Mã đơn hoặc SĐT') }}" class="border rounded px-3 py-1.5">
    <select name="status" class="border rounded px-2"><option value="">{{ __('Mọi trạng thái') }}</option>@foreach(\App\Models\Order::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status')==$k)>{{ __($l) }}</option>@endforeach</select>
    <button class="bg-gray-800 text-white px-4 rounded">{{ __('Lọc') }}</button></form>
<table class="w-full bg-white rounded-xl shadow-sm text-sm">
    <tr class="text-left text-gray-500"><th class="p-3">{{ __('Mã đơn') }}</th><th>{{ __('Khách') }}</th><th>{{ __('Ngày') }}</th><th>{{ __('Tổng') }}</th><th>{{ __('Thanh toán') }}</th><th>{{ __('Trạng thái') }}</th></tr>
    @foreach($orders as $o)
    <tr class="border-t"><td class="p-3"><a href="{{ route('admin.orders.show', $o) }}" class="text-indigo-600 font-medium">{{ $o->code }}</a></td>
        <td>{{ $o->name }}<div class="text-gray-400">{{ $o->phone }}</div></td><td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
        <td>{{ number_format($o->total, 0, ',', '.') }}₫</td><td>{{ strtoupper($o->payment_method) }} · {{ __(\App\Models\Order::PAYMENT_STATUSES[$o->payment_status]) }}</td>
        <td>{{ __(\App\Models\Order::STATUSES[$o->status]) }}</td></tr>
    @endforeach
</table>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
