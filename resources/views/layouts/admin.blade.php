<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@include('components.appearance-init')
<title>@yield('title', __('Quản trị')) - TechShop</title>
<script src="https://cdn.tailwindcss.com">
</script>
<script>tailwind.config={theme:{extend:{colors:{indigo:{50:'#f0f7f3',100:'#deeee5',600:'#246451',700:'#194d3d'}}}}};</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js">
</script>
<link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
<script defer src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}"></script>
</head>
<body class="admin-shell">
<aside class="admin-sidebar">
<a href="{{ route('admin.dashboard') }}" class="wordmark">techshop<span class="brand-dot">.</span>
</a>
<p class="eyebrow">{{ __('QUẢN TRỊ CỬA HÀNG') }}</p>
<nav aria-label="{{ __('Quản trị') }}">@foreach([['admin.dashboard','Tổng quan','filter'],['admin.products.index','Sản phẩm','bag'],['admin.catalog','Danh mục / Thuộc tính','keyboard'],['admin.orders.index','Đơn hàng','truck'],['admin.import','Nhập / Xuất CSV','arrow']] as [$r,$l,$icon])<a href="{{ route($r) }}" class="admin-nav-link {{ request()->routeIs($r) || ($r==='admin.products.index' && request()->routeIs('admin.products.*')) || ($r==='admin.orders.index' && request()->routeIs('admin.orders.*')) ? 'active' : '' }}">@include('components.icon',['name'=>$icon,'size'=>18]){{ __($l) }}</a>@endforeach</nav>
<a href="{{ route('home') }}" class="admin-nav-link admin-back">{{ __('← Về cửa hàng') }}</a>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="admin-nav-link">{{ __('Đăng xuất') }}</button>
</form>
</aside>
<main class="admin-main">
<div class="admin-topbar">
<span>{{ __('Không gian quản trị') }}</span>
<span>{{ auth()->user()->name }}</span>
@include('components.theme-toggle')
</div>@include('layouts.flash') @yield('content')</main>
</body>
</html>
