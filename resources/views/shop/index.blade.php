@extends('layouts.app')
@section('title', __($category->name ?? 'Sản phẩm'))
@section('content')
<nav class="breadcrumbs" aria-label="{{ __('Đường dẫn') }}">
<a href="{{ route('home') }}">{{ __('Trang chủ') }}</a>
<span>/</span>
<span>{{ __($category->name ?? 'Sản phẩm') }}</span>
</nav>
<div class="page-heading">
<p class="eyebrow">{{ __('CHỌN MÓN PHÙ HỢP VỚI BẠN') }}</p>
<h1>{{ __($category->name ?? 'Tất cả sản phẩm') }}</h1>
<p>{{ request('q') ? __('Kết quả tìm kiếm cho “:query”', ['query' => request('q')]) : __('Phụ kiện cho công việc, giải trí và những chuyến đi.') }}</p>
</div>
<div class="catalog-layout">
<details class="filter-panel" data-filter-panel open>
<summary>@include('components.icon', ['name'=>'filter','size'=>18]) {{ __('Bộ lọc sản phẩm') }}</summary>
<form method="GET" id="catalog-filter">
<input type="hidden" name="q" value="{{ request('q') }}">
<input type="hidden" name="sort" value="{{ request('sort') }}">
<input type="hidden" name="per_page" value="{{ $products->perPage() }}">
<div class="filter-group">
<h2>{{ __('Danh mục') }}</h2>
<div class="filter-categories">
<a href="{{ route('products.index') }}" class="filter-category {{ !$category ? 'active' : '' }}">{{ __('Tất cả sản phẩm') }}</a>@foreach($categories as $c)<a href="{{ route('category.show', $c->slug) }}" class="filter-category {{ $category?->id === $c->id ? 'active' : '' }}">{{ __($c->name) }}</a>@endforeach</div>
</div>
<div class="filter-group">
<label for="filter-brand">{{ __('Thương hiệu') }}</label>
<select name="brand" id="filter-brand">
<option value="">{{ __('Tất cả thương hiệu') }}</option>@foreach($brands as $b)<option value="{{ $b->id }}" @selected(request('brand') == $b->id)>{{ $b->name }}</option>@endforeach</select>
</div>
<div class="filter-group">
<h2>{{ __('Khoảng giá (₫)') }}</h2>
<div class="price-fields">
<label class="sr-only" for="filter-min">{{ __('Giá từ') }}</label>
<input id="filter-min" type="number" min="0" name="min" value="{{ request('min') }}" placeholder="{{ __('Giá từ') }}">
<label class="sr-only" for="filter-max">{{ __('Giá đến') }}</label>
<input id="filter-max" type="number" min="0" name="max" value="{{ request('max') }}" placeholder="{{ __('Giá đến') }}">
</div>
</div>
@foreach($filters as $f)<div class="filter-group">
<label for="filter-{{ $f->id }}">{{ __($f->name) }}</label>
<select id="filter-{{ $f->id }}" name="attr[{{ $f->id }}]">
<option value="">{{ __('Tất cả') }}</option>@foreach($f->choices as $opt)<option value="{{ $opt }}" @selected(request("attr.{$f->id}") == $opt)>{{ __($opt) }}</option>@endforeach</select>
</div>@endforeach
<button class="button button-primary filter-submit">{{ __('Áp dụng bộ lọc') }}</button>
<a class="filter-reset" href="{{ $category ? route('category.show', $category->slug) : route('products.index') }}">{{ __('Xóa bộ lọc') }}</a>
</form>
</details>
<div class="catalog-products">
<div class="catalog-topbar">
<span>{{ __(':count sản phẩm', ['count' => $products->total()]) }}{{ $products->total() ? __(' · Hiển thị :first–:last', ['first' => $products->firstItem(), 'last' => $products->lastItem()]) : '' }}</span>
<form method="GET" class="catalog-display-options">@foreach(request()->except('sort', 'page', 'per_page') as $key => $value)@if(is_array($value))@foreach($value as $subKey=>$subValue)<input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">@endforeach @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
<div>
<label for="catalog-per-page">{{ __('Mỗi trang') }}</label>
<select id="catalog-per-page" name="per_page" onchange="this.form.submit()">
@foreach([6, 12, 24] as $size)<option value="{{ $size }}" @selected($products->perPage() === $size)>{{ $size }}</option>@endforeach
</select>
</div>
<div>
<label class="sr-only" for="catalog-sort">{{ __('Sắp xếp sản phẩm') }}</label>
<select id="catalog-sort" name="sort" onchange="this.form.submit()">
<option value="">{{ __('Mới nhất') }}</option>
<option value="price_asc" @selected(request('sort')=='price_asc')>{{ __('Giá thấp đến cao') }}</option>
<option value="price_desc" @selected(request('sort')=='price_desc')>{{ __('Giá cao đến thấp') }}</option>
</select>
</div>
<noscript>
<button>{{ __('Sắp xếp') }}</button>
</noscript>
</form>
</div>
<div class="product-grid">@forelse($products as $p) @include('shop._card', ['p' => $p]) @empty <div class="empty-state">@include('components.icon', ['name'=>'search','size'=>36])<h2>{{ __('Chưa tìm thấy món phù hợp') }}</h2>
<p>{{ __('Thử từ khóa khác hoặc bỏ bớt điều kiện lọc.') }}</p>
<a class="button button-secondary" href="{{ $category ? route('category.show', $category->slug) : route('products.index') }}">{{ __('Xem lại sản phẩm') }}</a>
</div>@endforelse</div>
<div class="pagination">{{ $products->links() }}</div>
</div>
</div>
@endsection
