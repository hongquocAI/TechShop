<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TechShop') - Phụ kiện công nghệ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
<header class="bg-white shadow-sm sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-4">
        <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600">TechShop</a>
        <form action="{{ route('products.index') }}" class="flex-1 max-w-md">
            <input name="q" value="{{ request('q') }}" placeholder="Tìm tai nghe, sạc, cáp..." class="w-full border rounded-lg px-3 py-1.5 text-sm">
        </form>
        <nav class="flex items-center gap-4 text-sm">
            <a href="{{ route('products.index') }}" class="hover:text-indigo-600">Sản phẩm</a>
            <a href="{{ route('cart.index') }}" class="hover:text-indigo-600">Giỏ hàng
                <span class="bg-indigo-600 text-white rounded-full px-2 text-xs">{{ app(\App\Services\CartService::class)->count() }}</span></a>
            @auth
                @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-medium">Quản trị</a>@endif
                <a href="{{ route('account.orders') }}" class="hover:text-indigo-600">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-gray-500 hover:text-red-600">Thoát</button></form>
            @else
                <a href="{{ route('login') }}">Đăng nhập</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg">Đăng ký</a>
            @endauth
        </nav>
    </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6 flex-1 w-full">
    @include('layouts.flash')
    @yield('content')
</main>
<footer class="text-center text-sm text-gray-500 py-6 border-t bg-white">© {{ date('Y') }} TechShop - Đồ án website thương mại điện tử phụ kiện công nghệ</footer>
</body>
</html>
