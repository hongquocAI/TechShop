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
<div class="password-field">
<input id="register-password" class="field" name="password" type="password" autocomplete="new-password" autocapitalize="none" autocorrect="off" spellcheck="false" minlength="6" pattern="[\x20-\x7E]+" title="{{ __('Chỉ dùng chữ không dấu, số và ký tự đặc biệt.') }}" aria-describedby="register-password-help register-password-error" data-password-input required>
<button type="button" class="password-toggle" data-password-toggle aria-controls="register-password" aria-pressed="false" aria-label="{{ __('Hiện mật khẩu') }}" title="{{ __('Hiện mật khẩu') }}" hidden><span data-password-eye>@include('components.icon',['name'=>'eye'])</span><span data-password-eye-off hidden>@include('components.icon',['name'=>'eye-off'])</span></button>
</div>
<small id="register-password-help">{{ __('Ít nhất 6 ký tự, chỉ dùng chữ không dấu, số và ký tự đặc biệt.') }}</small>
<small id="register-password-error" class="password-error" role="status" hidden>{{ __('Mật khẩu không được chứa ký tự tiếng Việt hoặc ký tự có dấu. Hãy tắt bộ gõ tiếng Việt và nhập lại.') }}</small>
</div>
<div class="form-field">
<label for="register-confirm">{{ __('Nhập lại mật khẩu') }}</label>
<div class="password-field">
<input id="register-confirm" class="field" name="password_confirmation" type="password" autocomplete="new-password" autocapitalize="none" autocorrect="off" spellcheck="false" minlength="6" pattern="[\x20-\x7E]+" title="{{ __('Chỉ dùng chữ không dấu, số và ký tự đặc biệt.') }}" aria-describedby="register-confirm-error" data-password-input required>
<button type="button" class="password-toggle" data-password-toggle aria-controls="register-confirm" aria-pressed="false" aria-label="{{ __('Hiện mật khẩu nhập lại') }}" title="{{ __('Hiện mật khẩu nhập lại') }}" data-password-label="mật khẩu nhập lại" hidden><span data-password-eye>@include('components.icon',['name'=>'eye'])</span><span data-password-eye-off hidden>@include('components.icon',['name'=>'eye-off'])</span></button>
</div>
<small id="register-confirm-error" class="password-error" role="status" hidden>{{ __('Mật khẩu không được chứa ký tự tiếng Việt hoặc ký tự có dấu. Hãy tắt bộ gõ tiếng Việt và nhập lại.') }}</small>
</div>
<button class="button button-primary">{{ __('Tạo tài khoản') }} @include('components.icon',['name'=>'arrow','size'=>18])</button>
<div class="auth-alternative">{{ __('Đã có tài khoản?') }} <a href="{{ route('login') }}">{{ __('Đăng nhập') }}</a>
</div>
</form>
</div>
@endsection
