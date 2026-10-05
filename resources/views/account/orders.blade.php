@extends('layouts.app')
@section('title', 'Đơn hàng của tôi')
@section('content')
<h1 class="text-xl font-bold mb-4">Đơn hàng của tôi</h1>
<div class="bg-white rounded-xl shadow-sm divide-y text-sm">
    @forelse($orders as $o)
    <div class="p-4 flex items-center gap-4">
        <div class="flex-1"><a href="{{ route('order.done', $o->code) }}" class="font-semibold text-indigo-600">{{ $o->code }}</a>
            <div class="text-gray-500">{{ $o->created_at->format('d/m/Y H:i') }}</div></div>
        <span>{{ \App\Models\Order::STATUSES[$o->status] }}</span>
        <span class="text-gray-500">{{ \App\Models\Order::PAYMENT_STATUSES[$o->payment_status] }}</span>
        <span class="font-semibold w-28 text-right">{{ number_format($o->total, 0, ',', '.') }}₫</span>
        @if($o->canTransitionTo('cancelled') && $o->payment_status !== 'paid')
            <form method="POST" action="{{ route('account.cancel', $o->code) }}" onsubmit="return confirm('Hủy đơn này?')">@csrf<button class="text-red-500">Hủy</button></form>
        @endif
    </div>
    @empty <p class="p-4 text-gray-500">Bạn chưa có đơn hàng nào.</p> @endforelse
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
