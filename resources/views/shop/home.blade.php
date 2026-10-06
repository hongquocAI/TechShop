@extends('layouts.app')
@section('content')
@php $categoryBySlug = $categories->keyBy('slug'); @endphp
<section class="hero" data-carousel aria-roledescription="carousel" aria-label="{{ __('Gợi ý mua sắm') }}">
<div class="hero-slides" aria-live="off">
@foreach([
['tai-nghe', 'ÂM THANH CHO RIÊNG BẠN', 'Bật nhạc.', 'Tắt ồn ào.', 'Một playlist hay, một chiếc tai nghe vừa ý. Tìm bạn đồng hành cho những giờ làm việc và phút nghỉ ngơi.', 'Khám phá tai nghe', 'headphones', '01 / SOUND'],
['chuot-ban-phim', 'GÓC LÀM VIỆC MỖI NGÀY', 'Gọn góc bàn.', 'Dễ tập trung.', 'Chuột và bàn phím cho một góc làm việc dễ chịu. Chọn kết nối và mức giá phù hợp với bạn.', 'Chọn phụ kiện bàn làm việc', 'keyboard', '02 / DESK'],
['pin-du-phong', 'SẴN SÀNG CHO NGÀY DÀI', 'Nạp đầy pin.', 'Đi thật xa.', 'Mang theo năng lượng dự phòng. Khám phá pin sạc với dung lượng và số cổng bạn cần.', 'Khám phá pin dự phòng', 'powerbank', '03 / ON THE GO'],
] as $slide)
<article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-slide @if(!$loop->first) hidden inert @endif aria-label="{{ __(':number trên 3', ['number' => $loop->iteration]) }}">
<div class="hero-copy">
<p class="eyebrow">{{ __($slide[1]) }}</p>
<h1>{{ __($slide[2]) }}<br>
<span>{{ __($slide[3]) }}</span>
</h1>
<p class="hero-description">{{ __($slide[4]) }}</p>
<a href="{{ isset($categoryBySlug[$slide[0]]) ? route('category.show', $slide[0]) : route('products.index') }}" class="button button-primary">{{ __($slide[5]) }} @include('components.icon', ['name' => 'arrow', 'size' => 18])</a>
</div>
<div class="hero-art">
<span class="hero-orbit">
</span>
<span class="hero-caption">{{ $slide[7] }}</span>@include('components.device', ['device' => $slide[6], 'class' => 'hero-device', 'label' => __('Minh họa :name', ['name' => __($categoryBySlug[$slide[0]]->name ?? 'phụ kiện')])])<span class="hero-art-note">{{ __('Phụ kiện cho nhịp sống của bạn.') }}</span>
</div>
</article>
@endforeach
</div>
<div class="hero-controls">
<div class="slide-dots">@for($i = 0; $i < 3; $i++)<button type="button" data-slide-to="{{ $i }}" class="{{ $i === 0 ? 'is-active' : '' }}" aria-label="{{ __('Xem banner :number', ['number' => $i + 1]) }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">
<span>
</span>
</button>@endfor</div>
<span class="slide-count">01 / 03</span>
<div class="slide-actions">
<button type="button" data-slide-prev aria-label="{{ __('Banner trước') }}">@include('components.icon', ['name' => 'chevron', 'size' => 16])</button>
<button type="button" data-slide-next aria-label="{{ __('Banner tiếp theo') }}">@include('components.icon', ['name' => 'chevron', 'size' => 16])</button>
<button type="button" data-slide-pause data-pause-label="{{ __('Tạm dừng chuyển banner') }}" data-resume-label="{{ __('Tiếp tục chuyển banner') }}" aria-label="{{ __('Tạm dừng chuyển banner') }}" aria-pressed="false">@include('components.icon', ['name' => 'pause', 'size' => 15])</button>
</div>
</div>
</section>
<div class="service-strip">
<div>@include('components.icon', ['name' => 'shield', 'size' => 23])<span>
<strong>{{ __('Bảo hành 12 tháng') }}</strong>
<small>{{ __('An tâm sử dụng mỗi ngày') }}</small>
</span>
</div>
<div>@include('components.icon', ['name' => 'return', 'size' => 23])<span>
<strong>{{ __('Đổi trả trong 7 ngày') }}</strong>
<small>{{ __('An tâm khi chọn phụ kiện') }}</small>
</span>
</div>
<div>@include('components.icon', ['name' => 'truck', 'size' => 23])<span>
<strong>{{ __('Miễn phí giao từ :amount₫', ['amount' => number_format(config('payment.free_ship_from'), 0, ',', '.')]) }}</strong>
<small>{{ __('Phí giao hàng hiển thị trước khi đặt') }}</small>
</span>
</div>
</div>
<section class="home-categories">
<div class="section-heading">
<div>
<p class="eyebrow">{{ __('TÌM ĐÚNG MÓN BẠN CẦN') }}</p>
<h2>{{ __('Mua theo danh mục') }}</h2>
</div>
<a href="{{ route('products.index') }}" class="text-link">{{ __('Xem tất cả') }} @include('components.icon', ['name' => 'arrow', 'size' => 18])</a>
</div>
<div class="category-grid">@foreach($categories as $c)<a class="category-tile" href="{{ route('category.show', $c->slug) }}">
<span class="category-icon">@include('components.icon', ['name' => ['tai-nghe'=>'headphones','sac-cap'=>'plug','chuot-ban-phim'=>'keyboard','pin-du-phong'=>'battery'][$c->slug] ?? 'bag', 'size' => 30])</span>
<span>
<strong>{{ __($c->name) }}</strong>
<small>{{ __(':count sản phẩm', ['count' => $c->products_count]) }}</small>
</span>@include('components.icon', ['name' => 'arrow', 'size' => 18])</a>@endforeach</div>
</section>
<section class="featured-section">
<div class="section-heading">
<div>
<p class="eyebrow">{{ __('MỘT CHÚT NÂNG CẤP MỖI NGÀY') }}</p>
<h2>{{ __('Có gì mới ở TechShop?') }}</h2>
</div>
<a href="{{ route('products.index') }}" class="text-link">{{ __('Xem bộ sưu tập') }} @include('components.icon', ['name' => 'arrow', 'size' => 18])</a>
</div>
<div class="product-grid">@forelse($featured as $p) @include('shop._card', ['p' => $p]) @empty <div class="empty-state">
<h3>{{ __('Sản phẩm mới đang được cập nhật') }}</h3>
<p>{{ __('Quay lại sau để xem những phụ kiện mới nhất.') }}</p>
</div>@endforelse</div>
</section>
<section class="desk-story">
<div class="desk-story-art">@include('components.device', ['device' => 'mouse', 'class' => 'story-mouse'])@include('components.device', ['device' => 'keyboard', 'class' => 'story-keyboard'])<span>WORK, A LITTLE BETTER.</span>
</div>
<div class="desk-story-copy">
<p class="eyebrow">{{ __('CHO NHỮNG GIỜ TẬP TRUNG') }}</p>
<h2>{{ __('Góc bàn nhỏ.') }}<br>{{ __('Cảm hứng lớn.') }}</h2>
<p>{{ __('Không cần thay đổi mọi thứ. Bắt đầu với một chiếc chuột vừa tay, một bàn phím gõ êm và góc bàn bạn muốn ngồi lại.') }}</p>
<a href="{{ route('category.show', 'chuot-ban-phim') }}" class="text-link">{{ __('Chọn đồ cho góc làm việc') }} @include('components.icon', ['name' => 'arrow', 'size' => 18])</a>
</div>
</section>
@endsection
