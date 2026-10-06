<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#deeee5">
@include('components.appearance-init')
<title>@yield('title', 'TechShop') - {{ __('Phụ kiện công nghệ') }}</title>
<script src="https://cdn.tailwindcss.com">
</script>
<script>tailwind.config = {theme: {extend: {colors: {indigo: {50:'#f0f7f3',100:'#deeee5',600:'#246451',700:'#194d3d'}, purple: {600:'#246451'}}}}};</script>
<link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
<script defer src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}">
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js">
</script>
</head>
<body class="storefront">
<a href="#main-content" class="skip-link">{{ __('Bỏ qua menu, đến nội dung') }}</a>
<div class="announcement">
<div class="site-container">
<span>@include('components.icon', ['name' => 'truck', 'size' => 16]) {{ __('Miễn phí vận chuyển cho đơn từ :amount₫', ['amount' => number_format(config('payment.free_ship_from'), 0, ',', '.')]) }}</span>
<span class="announcement-note">{{ __('Những món nhỏ. Trải nghiệm tốt hơn.') }}</span>
</div>
</div>
<header class="site-header">
<div class="site-container header-main">
<a href="{{ route('home') }}" class="wordmark" aria-label="{{ __('TechShop — Trang chủ') }}">
<span class="brand-mark">t<span>:</span>
</span>techshop<span class="brand-dot">.</span>
</a>
<form action="{{ route('products.index') }}" method="GET" role="search" class="header-search">
<label class="sr-only" for="site-search">{{ __('Tìm sản phẩm') }}</label>
<input id="site-search" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Bạn đang tìm phụ kiện gì?') }}" autocomplete="off">
<button type="submit" aria-label="{{ __('Tìm kiếm') }}">@include('components.icon', ['name' => 'search'])</button>
</form>
<div class="header-actions">
@include('components.theme-toggle')
@auth
<div class="account-menu">
<button type="button" class="header-action" aria-label="{{ __('Tài khoản') }}" data-account-toggle aria-expanded="false" aria-controls="account-menu">@include('components.icon', ['name' => 'user'])<span>{{ __('Tài khoản') }}</span>
</button>
<div id="account-menu" class="account-dropdown" hidden>
<strong>{{ auth()->user()->name }}</strong>
<a href="{{ route('account.orders') }}">{{ __('Đơn hàng của tôi') }}</a>@if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}">{{ __('Trang quản trị') }}</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button>{{ __('Đăng xuất') }}</button>
</form>
</div>
</div>
@else
<a href="{{ route('login') }}" class="header-action" aria-label="{{ __('Đăng nhập') }}">@include('components.icon', ['name' => 'user'])<span>{{ __('Đăng nhập') }}</span>
</a>
@endauth
<a href="{{ route('cart.index') }}" class="header-action cart-link" aria-label="{{ __('Giỏ hàng, :count sản phẩm', ['count' => app(\App\Services\CartService::class)->count()]) }}">@include('components.icon', ['name' => 'bag'])<span>{{ __('Giỏ hàng') }}</span>
<b>{{ app(\App\Services\CartService::class)->count() }}</b>
</a>
</div>
</div>
<nav class="site-container category-nav" aria-label="{{ __('Danh mục sản phẩm') }}">
<a href="{{ route('products.index') }}" @class(['active' => request()->routeIs('products.index')])>{{ __('Tất cả sản phẩm') }}</a>@foreach(\App\Models\Category::all() as $navCategory)<a href="{{ route('category.show', $navCategory->slug) }}" @class(['active' => request()->routeIs('category.show') && request()->route('category')?->id === $navCategory->id])>{{ __($navCategory->name) }}</a>@endforeach<a href="{{ route('products.index', ['sort' => 'price_asc']) }}" class="nav-tip">{{ __('Chọn theo ngân sách') }} @include('components.icon', ['name' => 'arrow', 'size' => 15])</a>
</nav>
</header>
<main id="main-content" class="site-container main-content">@include('layouts.flash') @yield('content')</main>
<footer class="site-footer">
<div class="site-container footer-main">
<div>
<a href="{{ route('home') }}" class="wordmark">techshop<span class="brand-dot">.</span>
</a>
<p>{{ __('Phụ kiện vừa ý.') }}<br>{{ __('Mỗi ngày thuận tiện hơn.') }}</p>
</div>
<div>
<h2>{{ __('Khám phá') }}</h2>
<a href="{{ route('products.index') }}">{{ __('Tất cả sản phẩm') }}</a>@foreach(\App\Models\Category::all() as $footerCategory)<a href="{{ route('category.show', $footerCategory->slug) }}">{{ __($footerCategory->name) }}</a>@endforeach</div>
<div>
<h2>{{ __('Mua sắm cùng TechShop') }}</h2>
<a href="{{ route('cart.index') }}">{{ __('Giỏ hàng của bạn') }}</a>
<a href="{{ auth()->check() ? route('account.orders') : route('login') }}">{{ __('Theo dõi đơn hàng') }}</a>@guest<a href="{{ route('register') }}">{{ __('Tạo tài khoản') }}</a>@endguest<p>{{ __('Thanh toán COD · VNPay') }}</p>
</div>
<div class="footer-shipping">@include('components.icon', ['name' => 'truck', 'size' => 30])<h2>{{ __('Giao đến tận cửa') }}</h2>
<p>{{ __('Phí giao hàng :amount₫.', ['amount' => number_format(config('payment.shipping_fee', 30000), 0, ',', '.')]) }}<br>{{ __('Miễn phí từ :amount₫.', ['amount' => number_format(config('payment.free_ship_from'), 0, ',', '.')]) }}</p>
</div>
</div>
<section class="site-container footer-map" aria-labelledby="footer-map-title">
<div class="footer-map-heading">
<h2 id="footer-map-title">{{ __('Vị trí trên bản đồ') }}</h2>
<p>{{ __('Trường Đại học Công Thương TP. Hồ Chí Minh (HUIT)') }}</p>
</div>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31352.538163214747!2d106.60746295150906!3d10.806159823201295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752be27d8b4f4d%3A0x92dcba2950430867!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBUaMawxqFuZyBUUC4gSOG7kyBDaMOtIE1pbmggKEhVSVQp!5e0!3m2!1svi!2s!4v1791272257503!5m2!1svi!2s" title="{{ __('Bản đồ Trường Đại học Công Thương TP. Hồ Chí Minh') }}" width="600" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
</section>
<div class="site-container footer-bottom">
<span>© {{ date('Y') }} TechShop</span>
<span>{{ __('Website học tập · Thanh toán VNPay có chế độ giả lập') }}</span>
<a href="#main-content">{{ __('Lên đầu trang ↑') }}</a>
</div>
</footer>
</body>
</html>
