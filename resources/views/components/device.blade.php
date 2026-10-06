<svg class="{{ $class ?? 'device-art' }}" viewBox="0 0 400 400" role="img" aria-label="{{ $label ?? 'Hình minh họa phụ kiện' }}">
<use href="{{ asset('images/devices.svg') }}?v={{ filemtime(public_path('images/devices.svg')) }}#{{ $device ?? 'headphones' }}"/>
</svg>
