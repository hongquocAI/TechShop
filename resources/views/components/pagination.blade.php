@if($paginator->count())
@php
$current = $paginator->currentPage();
$last = $paginator->lastPage();
$pages = collect([1, $current - 1, $current, $current + 1, $last])
    ->filter(fn ($page) => $page >= 1 && $page <= $last)->unique()->sort()->values();
$previous = null;
@endphp
<nav class="pagination-nav" aria-label="{{ __('Phân trang') }}">
<p class="pagination-caption">{{ __('Trang :current / :last', ['current' => $current, 'last' => $last]) }}</p>
<ul class="pagination-list">
<li>
@if($paginator->onFirstPage())
<span class="pagination-link is-disabled" aria-disabled="true" aria-label="{{ __('Trang trước') }}">@include('components.icon', ['name' => 'chevron', 'size' => 18])</span>
@else
<a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Trang trước') }}" title="{{ __('Trang trước') }}">@include('components.icon', ['name' => 'chevron', 'size' => 18])</a>
@endif
</li>
@foreach($pages as $page)
@if($previous !== null && $page - $previous > 1)<li class="pagination-gap" aria-hidden="true">…</li>@endif
<li>
@if($page === $current)
<span class="pagination-link is-current" aria-current="page" aria-label="{{ __('Trang :number', ['number' => $page]) }}">{{ $page }}</span>
@else
<a class="pagination-link" href="{{ $paginator->url($page) }}" aria-label="{{ __('Trang :number', ['number' => $page]) }}">{{ $page }}</a>
@endif
</li>
@php $previous = $page; @endphp
@endforeach
<li>
@if($paginator->hasMorePages())
<a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Trang sau') }}" title="{{ __('Trang sau') }}">@include('components.icon', ['name' => 'chevron', 'size' => 18])</a>
@else
<span class="pagination-link is-disabled" aria-disabled="true" aria-label="{{ __('Trang sau') }}">@include('components.icon', ['name' => 'chevron', 'size' => 18])</span>
@endif
</li>
</ul>
</nav>
@endif
