@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<form method="POST" class="max-w-sm mx-auto bg-white rounded-xl p-6 shadow-sm space-y-3 text-sm">@csrf
    <h1 class="text-xl font-bold">Đăng nhập</h1>
    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="w-full border rounded px-3 py-2" required>
    <input name="password" type="password" placeholder="Mật khẩu" class="w-full border rounded px-3 py-2" required>
    <label class="flex items-center gap-2"><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>
    <button class="w-full bg-indigo-600 text-white rounded-lg py-2">Đăng nhập</button>
    <div class="text-center">Chưa có tài khoản? <a href="{{ route('register') }}" class="text-indigo-600">Đăng ký</a></div>
</form>
@endsection
