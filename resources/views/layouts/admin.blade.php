<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị') - TechShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex min-h-screen">
<aside class="w-56 bg-gray-900 text-gray-200 p-4 text-sm space-y-1 shrink-0">
    <div class="text-lg font-bold text-white mb-4">TechShop Admin</div>
    @foreach([
        ['admin.dashboard','Tổng quan'],['admin.products.index','Sản phẩm'],['admin.catalog','Danh mục / Thuộc tính'],
        ['admin.orders.index','Đơn hàng'],['admin.import','Nhập / Xuất CSV'],
    ] as [$r,$l])
        <a href="{{ route($r) }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs($r) ? 'bg-gray-700' : '' }}">{{ $l }}</a>
    @endforeach
    <a href="{{ route('home') }}" class="block px-3 py-2 rounded hover:bg-gray-700 mt-6 text-gray-400">← Về cửa hàng</a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="px-3 py-2 text-gray-400 hover:text-white">Đăng xuất</button></form>
</aside>
<main class="flex-1 p-6 overflow-x-auto">
    @include('layouts.flash')
    @yield('content')
</main>
</body>
</html>
