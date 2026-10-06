@extends('layouts.app')
@section('title', __('Cổng thanh toán giả lập'))
@section('content')
<div class="payment-demo">
<p class="eyebrow">{{ __('TRẢI NGHIỆM THANH TOÁN') }}</p>
<h1>{{ __('Cổng VNPay giả lập') }}</h1>
<p class="summary-note">{{ __('Đây là bước mô phỏng cho website demo. Chọn một kết quả để xem trạng thái đơn hàng.') }}</p>
<div class="demo-total"><span>{{ __('Đơn :code', ['code' => $tx->order->code]) }}</span><strong>{{ number_format($tx->amount, 0, ',', '.') }}₫</strong></div>
<form method="POST" action="{{ route('payment.mock.pay', $tx->transaction_code) }}">@csrf
<button name="result" value="success" class="button button-primary">{{ __('Mô phỏng thành công') }}</button>
<button name="result" value="fail" class="button button-secondary">{{ __('Mô phỏng thất bại') }}</button>
</form>
</div>
@endsection
