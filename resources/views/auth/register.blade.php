@extends('layouts.app')
@section('title', 'Đăng ký')
@section('content')
<form method="POST" class="max-w-sm mx-auto bg-white rounded-xl p-6 shadow-sm space-y-3 text-sm">@csrf
    <h1 class="text-xl font-bold">Đăng ký</h1>
    <input name="name" value="{{ old('name') }}" placeholder="Họ tên" class="w-full border rounded px-3 py-2" required>
    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="w-full border rounded px-3 py-2" required>
    <input name="phone" value="{{ old('phone') }}" placeholder="Số điện thoại" class="w-full border rounded px-3 py-2">
    <input name="password" type="password" placeholder="Mật khẩu (≥ 6 ký tự)" class="w-full border rounded px-3 py-2" required>
    <input name="password_confirmation" type="password" placeholder="Nhập lại mật khẩu" class="w-full border rounded px-3 py-2" required>
    <button class="w-full bg-indigo-600 text-white rounded-lg py-2">Tạo tài khoản</button>
</form>
@endsection
