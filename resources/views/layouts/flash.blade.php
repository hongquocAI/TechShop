@if(session('success'))<div class="flash-message" role="status">@include('components.icon',['name'=>'check','size'=>18])<span>{{ session('success') }}</span>
</div>@endif
@if(session('error'))<div class="flash-message error" role="alert">
<span>{{ session('error') }}</span>
</div>@endif
@if($errors->any())<div class="flash-message error" role="alert">
<div>
<strong>{{ __('Vui lòng kiểm tra lại thông tin:') }}</strong>
<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
</div>@endif
