@extends('layouts.admin')
@section('title', 'Tổng quan')
@section('content')
<h1 class="text-xl font-bold mb-4">Tổng quan</h1>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 text-sm">
    @foreach([['Doanh thu (đã thanh toán)', number_format($revenue, 0, ',', '.').'₫'], ['Tổng đơn hàng', $orderCount], ['Sản phẩm', $productCount], ['Độ hoàn thiện dữ liệu TB', $avgCompleteness.'%']] as [$l, $v])
        <div class="bg-white rounded-xl p-4 shadow-sm"><div class="text-gray-500">{{ $l }}</div><div class="text-2xl font-bold mt-1">{{ $v }}</div></div>
    @endforeach
</div>
<div class="grid lg:grid-cols-2 gap-6 text-sm">
    <div class="bg-white rounded-xl p-4 shadow-sm"><div class="font-semibold mb-2">Đơn hàng theo trạng thái</div>
        @foreach(\App\Models\Order::STATUSES as $k => $l)<div class="flex justify-between border-t py-1"><span>{{ $l }}</span><span>{{ $byStatus[$k] ?? 0 }}</span></div>@endforeach</div>
    <div class="bg-white rounded-xl p-4 shadow-sm"><div class="font-semibold mb-2">Thanh toán thành công theo cổng</div>
        @forelse($gateways as $g)<div class="flex justify-between border-t py-1"><span>{{ strtoupper($g->gateway) }}</span><span>{{ $g->ok }}/{{ $g->total }} ({{ $g->total ? round($g->ok / $g->total * 100) : 0 }}%)</span></div>@empty<p class="text-gray-500">Chưa có giao dịch.</p>@endforelse</div>
    <div class="bg-white rounded-xl p-4 shadow-sm"><div class="font-semibold mb-2">Sản phẩm bán chạy</div>
        @forelse($topProducts as $t)<div class="flex justify-between border-t py-1"><span>{{ $t->name }}</span><span>{{ $t->qty }}</span></div>@empty<p class="text-gray-500">Chưa có dữ liệu.</p>@endforelse</div>
    <div class="bg-white rounded-xl p-4 shadow-sm"><div class="font-semibold mb-2">Sắp hết hàng (≤ 5)</div>
        @foreach($lowStock as $p)<div class="flex justify-between border-t py-1"><a href="{{ route('admin.products.edit', $p) }}">{{ $p->name }}</a><span class="text-red-600">{{ $p->stock }}</span></div>@endforeach</div>
    <div class="bg-white rounded-xl p-4 shadow-sm lg:col-span-2"><div class="font-semibold mb-2">Sản phẩm dữ liệu chưa hoàn thiện (PIM)</div>
        @foreach($incomplete as $p)<div class="flex justify-between border-t py-1"><a href="{{ route('admin.products.edit', $p) }}">{{ $p->name }}</a><span>{{ $p->completeness }}%</span></div>@endforeach</div>
</div>
@endsection
