@extends('layouts.app')
@section('title', __($category->name ?? 'Sản phẩm'))
@section('content')
@php
    $categoryIcons = ['tai-nghe'=>'headphones', 'sac-cap'=>'plug', 'chuot-ban-phim'=>'keyboard', 'pin-du-phong'=>'battery'];
    $catalogDevice = $category ? (['tai-nghe'=>'headphones', 'sac-cap'=>'charger', 'chuot-ban-phim'=>'keyboard', 'pin-du-phong'=>'powerbank'][$category->slug] ?? null) : 'headphones';
@endphp
<div class="catalog-page">
<nav class="breadcrumbs" aria-label="{{ __('Đường dẫn') }}">
<a href="{{ route('home') }}">{{ __('Trang chủ') }}</a>
<span>/</span>
<span>{{ __($category->name ?? 'Sản phẩm') }}</span>
</nav>
<section class="catalog-intro" aria-labelledby="catalog-title">
<div class="catalog-intro-copy">
<p class="eyebrow"><span></span>{{ __('PHỤ KIỆN CHO MỖI NGÀY') }}</p>
<h1 id="catalog-title">{{ request('q') ? __('Kết quả tìm kiếm') : __($category->name ?? 'Tất cả sản phẩm') }}</h1>
<p>{{ request('q') ? __('Kết quả tìm kiếm cho “:query”', ['query' => request('q')]) : __('Phụ kiện cho công việc, giải trí và những chuyến đi.') }}</p>
<span class="catalog-intro-note">{{ __('Chọn đúng món. Dùng thật lâu.') }}</span>
</div>
<div class="catalog-intro-art" aria-hidden="true">
<span class="catalog-art-line"></span>
@if($catalogPreview)
<img src="{{ $catalogPreview->image_url }}" alt="" class="catalog-category-photo">
@elseif($catalogDevice)
@include('components.device', ['device'=>$catalogDevice, 'class'=>'catalog-main-device'])
@else
<span class="catalog-neutral-art">@include('components.icon', ['name'=>'bag','size'=>64])</span>
@endif
@if(!$category) @include('components.device', ['device'=>'earbuds', 'class'=>'catalog-small-device']) @endif
<span class="catalog-art-caption">{{ __($category->name ?? 'Những món nhỏ, tiện ích lớn.') }}</span>
</div>
</section>
<nav class="catalog-category-strip" aria-label="{{ __('Chọn danh mục sản phẩm') }}">
<a href="{{ route('products.index') }}" @class(['catalog-category', 'is-active'=>!$category]) @if(!$category) aria-current="page" @endif>
<span class="catalog-category-icon">@include('components.icon', ['name'=>'bag','size'=>23])</span>
<span><strong>{{ __('Tất cả') }}</strong><small>{{ __(':count sản phẩm', ['count'=>$categories->sum('products_count')]) }}</small></span>
</a>
@foreach($categories as $c)
<a href="{{ route('category.show', $c->slug) }}" @class(['catalog-category', 'is-active'=>$category?->id === $c->id]) @if($category?->id === $c->id) aria-current="page" @endif>
<span class="catalog-category-icon">@include('components.icon', ['name'=>$categoryIcons[$c->slug] ?? 'bag','size'=>23])</span>
<span><strong>{{ __($c->name) }}</strong><small>{{ __(':count sản phẩm', ['count'=>$c->products_count]) }}</small></span>
</a>
@endforeach
</nav>
<div class="catalog-layout">
<details class="filter-panel" data-filter-panel open>
<summary>@include('components.icon', ['name'=>'filter','size'=>18]) {{ __('Bộ lọc sản phẩm') }}@if(count($activeFilters))<span class="filter-count">{{ count($activeFilters) }}</span>@endif</summary>
<form method="GET" id="catalog-filter">
<input type="hidden" name="q" value="{{ request('q') }}">
<input type="hidden" name="sort" value="{{ request('sort') }}">
<input type="hidden" name="per_page" value="{{ $products->perPage() }}">
<p class="filter-intro">{{ __('Thu hẹp lựa chọn theo nhu cầu của bạn.') }}</p>
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
@if(count($activeFilters))<a class="filter-reset" href="{{ $clearFiltersUrl }}">{{ __('Xóa bộ lọc') }}</a>@endif
</form>
</details>
<div class="catalog-products">
<div class="catalog-topbar">
<div class="catalog-result-count"><strong>{{ __(':count sản phẩm', ['count' => $products->total()]) }}</strong><span>{{ $products->total() ? __('Hiển thị :first–:last', ['first' => $products->firstItem(), 'last' => $products->lastItem()]) : __('Thử điều chỉnh bộ lọc') }}</span></div>
<form method="GET" class="catalog-display-options">@foreach(request()->except('sort', 'page', 'per_page') as $key => $value)@if(is_array($value))@foreach($value as $subKey=>$subValue)<input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">@endforeach @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
<div>
<label for="catalog-per-page">{{ __('Mỗi trang') }}</label>
<select id="catalog-per-page" name="per_page" onchange="this.form.submit()">
@foreach([6, 12, 24] as $size)<option value="{{ $size }}" @selected($products->perPage() === $size)>{{ $size }}</option>@endforeach
</select>
</div>
<div>
<label for="catalog-sort">{{ __('Sắp xếp') }}</label>
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
@if(count($activeFilters))
<div class="catalog-active-filters" aria-label="{{ __('Điều kiện đang áp dụng') }}">
@foreach($activeFilters as $filter)<a class="catalog-filter-chip" href="{{ $filter['url'] }}" aria-label="{{ __('Bỏ điều kiện :label', ['label'=>$filter['label']]) }}"><span>{{ $filter['label'] }}</span>@include('components.icon', ['name'=>'close','size'=>14])</a>@endforeach
<a class="catalog-clear-filters" href="{{ $clearFiltersUrl }}">{{ __('Xóa tất cả') }}</a>
</div>
@endif
<div class="product-grid">@forelse($products as $p) @include('shop._card', ['p' => $p]) @empty <div class="empty-state">@include('components.icon', ['name'=>'search','size'=>36])<h2>{{ __('Chưa tìm thấy món phù hợp') }}</h2>
<p>{{ __('Thử từ khóa khác hoặc bỏ bớt điều kiện lọc.') }}</p>
<a class="button button-secondary" href="{{ $category ? route('category.show', $category->slug) : route('products.index') }}">{{ __('Xem lại sản phẩm') }}</a>
</div>@endforelse</div>
<div class="pagination">{{ $products->links() }}</div>
</div>
</div>
</div>
@endsection
