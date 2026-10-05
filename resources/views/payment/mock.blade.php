@extends('layouts.app')
@section('title', 'Cổng thanh toán giả lập')
@section('content')
<div class="max-w-md mx-auto bg-white rounded-xl p-6 shadow-sm text-center">
    <div class="text-xs bg-yellow-100 text-yellow-800 rounded px-2 py-1 inline-block mb-3">Cổng giả lập — dùng để demo khi chưa cấu hình VNPay sandbox</div>
    <h1 class="text-xl font-bold">Thanh toán đơn {{ $tx->order->code }}</h1>
    <div class="text-3xl font-bold text-red-600 my-4">{{ number_format($tx->amount, 0, ',', '.') }}₫</div>
    <form method="POST" action="{{ route('payment.mock.pay', $tx->transaction_code) }}" class="flex gap-3 justify-center">@csrf
        <button name="result" value="success" class="bg-green-600 text-white px-5 py-2 rounded-lg">Thanh toán thành công</button>
        <button name="result" value="fail" class="bg-gray-500 text-white px-5 py-2 rounded-lg">Hủy / thất bại</button>
    </form>
</div>
@endsection
