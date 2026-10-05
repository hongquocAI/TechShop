<a href="{{ route('products.show', $p->slug) }}" class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden block">
    <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full aspect-square object-cover">
    <div class="p-3">
        <div class="text-xs text-gray-500">{{ $p->brand?->name }}</div>
        <div class="font-medium text-sm line-clamp-2 h-10">{{ $p->name }}</div>
        <div class="mt-2 flex items-baseline gap-2">
            <span class="text-red-600 font-bold">{{ number_format($p->final_price, 0, ',', '.') }}₫</span>
            @if($p->final_price < $p->price)<span class="text-xs text-gray-400 line-through">{{ number_format($p->price, 0, ',', '.') }}₫</span>@endif
        </div>
        @if($p->stock <= 0)<div class="text-xs text-red-500 mt-1">Hết hàng</div>@endif
    </div>
</a>
