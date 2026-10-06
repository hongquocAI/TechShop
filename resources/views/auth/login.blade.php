@extends('layouts.app')
@section('title', __('Đăng nhập'))
@section('content')
<div class="auth-layout">
<aside class="auth-aside">
<p class="eyebrow">{{ __('CHÀO BẠN TRỞ LẠI') }}</p>
<h2>{{ __('Món bạn thích.') }}<br>{{ __('Luôn trong tầm tay.') }}</h2>
<p>{{ __('Đăng nhập để xem lại đơn hàng và đặt hàng thuận tiện hơn.') }}</p>@include('components.device',['device'=>'earbuds'])</aside>
<form method="POST" class="auth-form">@csrf<h1>{{ __('Đăng nhập') }}</h1>
<p>{{ __('Tiếp tục mua sắm cùng TechShop.') }}</p>
<div class="form-field">
<label for="login-email">Email</label>
<input id="login-email" class="field" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required>
</div>
<div class="form-field">
<label for="login-password">{{ __('Mật khẩu') }}</label>
<div class="password-field">
<input id="login-password" class="field" name="password" type="password" autocomplete="current-password" autocapitalize="none" autocorrect="off" spellcheck="false" pattern="[\x20-\x7E]+" title="{{ __('Chỉ dùng chữ không dấu, số và ký tự đặc biệt.') }}" aria-describedby="login-password-help login-password-error" data-password-input required>
<button type="button" class="password-toggle" data-password-toggle aria-controls="login-password" aria-pressed="false" aria-label="{{ __('Hiện mật khẩu') }}" title="{{ __('Hiện mật khẩu') }}" hidden><span data-password-eye>@include('components.icon',['name'=>'eye'])</span><span data-password-eye-off hidden>@include('components.icon',['name'=>'eye-off'])</span></button>
</div>
<small id="login-password-help">{{ __('Chỉ dùng chữ không dấu, số và ký tự đặc biệt.') }}</small>
<small id="login-password-error" class="password-error" role="status" hidden>{{ __('Mật khẩu không được chứa ký tự tiếng Việt hoặc ký tự có dấu. Hãy tắt bộ gõ tiếng Việt và nhập lại.') }}</small>
</div>
<label class="remember">
<input type="checkbox" name="remember" @checked(old('remember'))>{{ __('Ghi nhớ đăng nhập') }}</label>
<button class="button button-primary">{{ __('Đăng nhập') }} @include('components.icon',['name'=>'arrow','size'=>18])</button>
<div class="auth-alternative">{{ __('Chưa có tài khoản?') }} <a href="{{ route('register') }}">{{ __('Tạo tài khoản') }}</a>
</div>
</form>
</div>
@endsection
