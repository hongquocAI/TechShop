<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

// ---------- Storefront ----------
Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/san-pham', [ShopController::class, 'index'])->name('products.index');
Route::get('/danh-muc/{category:slug}', [ShopController::class, 'index'])->name('category.show');
Route::get('/san-pham/{slug}', [ShopController::class, 'show'])->name('products.show');

// ---------- Giỏ hàng & thanh toán ----------
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-hang/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{product}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/thanh-toan', [CheckoutController::class, 'form'])->name('checkout.form');
Route::post('/thanh-toan', [CheckoutController::class, 'place'])->middleware('throttle:10,1')->name('checkout.place');
Route::get('/don-hang/{code}', [CheckoutController::class, 'done'])->name('order.done');

Route::get('/payment/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
Route::get('/payment/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payment.vnpay.ipn');
Route::get('/payment/mock/{code}', [PaymentController::class, 'mock'])->name('payment.mock');
Route::post('/payment/mock/{code}', [PaymentController::class, 'mockPay'])->name('payment.mock.pay');

// ---------- Tài khoản ----------
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/dang-ky', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);
});
Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/tai-khoan/don-hang', [AccountController::class, 'orders'])->name('account.orders');
    Route::post('/tai-khoan/don-hang/{code}/huy', [AccountController::class, 'cancel'])->name('account.cancel');
});

// ---------- Quản trị ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminOnly::class])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', Admin\ProductController::class)->except('show');

    Route::get('catalog', [Admin\CatalogController::class, 'index'])->name('catalog');
    Route::post('categories', [Admin\CatalogController::class, 'storeCategory'])->name('categories.store');
    Route::delete('categories/{category}', [Admin\CatalogController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('brands', [Admin\CatalogController::class, 'storeBrand'])->name('brands.store');
    Route::delete('brands/{brand}', [Admin\CatalogController::class, 'destroyBrand'])->name('brands.destroy');
    Route::post('attributes', [Admin\CatalogController::class, 'storeAttribute'])->name('attributes.store');
    Route::delete('attributes/{attribute}', [Admin\CatalogController::class, 'destroyAttribute'])->name('attributes.destroy');

    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('import-export', [Admin\ImportExportController::class, 'form'])->name('import');
    Route::get('export', [Admin\ImportExportController::class, 'export'])->name('export');
    Route::post('import', [Admin\ImportExportController::class, 'import'])->name('import.run');
});
