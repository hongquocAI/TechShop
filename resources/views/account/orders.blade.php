@extends('layouts.app')
@section('title', __('Đơn hàng của tôi'))
@section('content')
<div class="page-heading">
<p class="eyebrow">{{ __('TÀI KHOẢN CỦA BẠN') }}</p>
<h1>{{ __('Đơn hàng của tôi') }}</h1>
<p>{{ __('Theo dõi trạng thái và xem lại những món đã đặt.') }}</p>
</div>
<div class="account-orders">@forelse($orders as $o)<article class="account-order">
<div>
<a class="order-code" href="{{ route('order.done',$o->code) }}">{{ $o->code }}</a>
<p>{{ $o->created_at->format('d/m/Y · H:i') }}</p>
</div>
<div class="order-states">
<span class="status-chip">{{ __(\App\Models\Order::STATUSES[$o->status]) }}</span>
<span class="payment-status">{{ __(\App\Models\Order::PAYMENT_STATUSES[$o->payment_status]) }}</span>
</div>
<strong>{{ number_format($o->total,0,',','.') }}₫</strong>
<div class="order-actions">
<a href="{{ route('order.done',$o->code) }}" class="text-link">{{ __('Xem chi tiết') }} @include('components.icon',['name'=>'arrow','size'=>15])</a>@if($o->canTransitionTo('cancelled') && $o->payment_status !== 'paid')<form method="POST" action="{{ route('account.cancel',$o->code) }}" data-confirm="{{ __('Bạn muốn hủy đơn :code?', ['code' => $o->code]) }}">@csrf<button class="link-button remove-button">{{ __('Hủy đơn') }}</button>
</form>@endif</div>
</article>@empty<div class="empty-state">@include('components.icon',['name'=>'bag','size'=>38])<h2>{{ __('Chưa có đơn hàng nào') }}</h2>
<p>{{ __('Món phụ kiện đầu tiên của bạn đang chờ được khám phá.') }}</p>
<a href="{{ route('products.index') }}" class="button button-primary">{{ __('Bắt đầu mua sắm') }}</a>
</div>@endforelse</div>
<div class="pagination">{{ $orders->links() }}</div>
@endsection
