@extends('layouts.app')
@section('title', __('Đăng ký'))
@section('content')
<div class="auth-layout">
<aside class="auth-aside">
<p class="eyebrow">{{ __('LÀM QUEN VỚI TECHSHOP') }}</p>
<h2>{{ __('Mỗi ngày,') }}<br>{{ __('thêm thuận tiện.') }}</h2>
<p>{{ __('Tạo tài khoản để theo dõi đơn hàng và lưu thông tin cho lần mua tiếp theo.') }}</p>@include('components.device',['device'=>'headphones'])</aside>
<form method="POST" class="auth-form">@csrf<h1>{{ __('Tạo tài khoản') }}</h1>
<p>{{ __('Chỉ vài thông tin để bắt đầu.') }}</p>
<div class="form-field">
<label for="register-name">{{ __('Họ và tên') }}</label>
<input id="register-name" class="field" name="name" autocomplete="name" value="{{ old('name') }}" required>
</div>
<div class="form-field">
<label for="register-email">Email</label>
<input id="register-email" class="field" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
</div>
<div class="form-field">
<label for="register-phone">{{ __('Số điện thoại (tùy chọn)') }}</label>
<input id="register-phone" class="field" type="tel" name="phone" autocomplete="tel" value="{{ old('phone') }}">
</div>
<div class="form-field">
<label for="register-password">{{ __('Mật khẩu') }}</label>
<input id="register-password" class="field" name="password" type="password" autocomplete="new-password" minlength="6" required>
<small>{{ __('Ít nhất 6 ký tự.') }}</small>
</div>
<div class="form-field">
<label for="register-confirm">{{ __('Nhập lại mật khẩu') }}</label>
<input id="register-confirm" class="field" name="password_confirmation" type="password" autocomplete="new-password" minlength="6" required>
</div>
<button class="button button-primary">{{ __('Tạo tài khoản') }} @include('components.icon',['name'=>'arrow','size'=>18])</button>
<div class="auth-alternative">{{ __('Đã có tài khoản?') }} <a href="{{ route('login') }}">{{ __('Đăng nhập') }}</a>
</div>
</form>
</div>
@endsection
