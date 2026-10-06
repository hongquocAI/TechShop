<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShopController extends Controller
{
    public function home()
    {
        return view('shop.home', [
            'featured' => Product::published()->with(['brand', 'category'])->where('stock', '>', 0)->latest()->orderByDesc('id')->take(8)->get(),
            'categories' => Category::withCount(['products' => fn ($q) => $q->published()])->get(),
        ]);
    }

    public function index(Request $request, ?Category $category = null)
    {
        $q = Product::published()->with(['brand', 'category']);

        if ($category) $q->where('category_id', $category->id);
        if ($s = $request->query('q')) $q->where('name', 'like', "%{$s}%");
        if ($b = $request->query('brand')) $q->where('brand_id', $b);
        if ($min = $request->query('min')) $q->whereRaw('COALESCE(sale_price, price) >= ?', [(int) $min]);
        if ($max = $request->query('max')) $q->whereRaw('COALESCE(sale_price, price) <= ?', [(int) $max]);

        // Lọc theo thuộc tính động (is_filterable): ?attr[5]=Bluetooth 5.3
        foreach ((array) $request->query('attr', []) as $attrId => $val) {
            if ($val !== '' && $val !== null) {
                $q->whereHas('attributeValues', fn ($v) => $v->where('attribute_id', $attrId)->where('value', $val));
            }
        }

        match ($request->query('sort')) {
            'price_asc' => $q->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $q->orderByRaw('COALESCE(sale_price, price) desc'),
            default => $q->latest(),
        };
        // Use a stable tie-breaker so products do not repeat between pages with equal dates/prices.
        $q->orderByDesc('id');
        $perPage = (int) $request->query('per_page', 6);
        if (!in_array($perPage, [6, 12, 24], true)) $perPage = 6;

        // Bộ lọc thuộc tính hiển thị theo danh mục đang xem
        $filters = $category
            ? $category->attributes()->where('is_filterable', true)->get()->map(function ($a) {
                $a->choices = $a->type === 'select' && $a->options ? $a->options
                    : \App\Models\ProductAttributeValue::where('attribute_id', $a->id)->distinct()->pluck('value')->all();
                return $a;
            })
            : collect();

        $products = $q->paginate($perPage)->withQueryString();
        if ($products->currentPage() > $products->lastPage()) {
            return redirect()->to($products->url($products->lastPage()));
        }

        $brands = Brand::orderBy('name')->get();
        $activeFilters = [];
        $addFilter = function (string $key, string $label) use ($request, &$activeFilters) {
            $query = $request->except('page');
            Arr::forget($query, $key);
            if (isset($query['attr']) && $query['attr'] === []) unset($query['attr']);
            $activeFilters[] = [
                'label' => $label,
                'url' => $request->url().($query ? '?'.http_build_query($query) : ''),
            ];
        };
        if ($request->filled('q')) $addFilter('q', 'Tìm: '.$request->query('q'));
        if ($brand = $brands->firstWhere('id', $request->query('brand'))) $addFilter('brand', $brand->name);
        if ($request->filled('min')) $addFilter('min', 'Từ '.number_format((int) $request->query('min'), 0, ',', '.').'₫');
        if ($request->filled('max')) $addFilter('max', 'Đến '.number_format((int) $request->query('max'), 0, ',', '.').'₫');
        foreach ($filters as $filter) {
            if ($request->filled("attr.{$filter->id}")) {
                $addFilter("attr.{$filter->id}", $filter->name.': '.$request->input("attr.{$filter->id}"));
            }
        }
        $displayQuery = $request->only('sort', 'per_page');
        $clearFiltersUrl = $request->url().($displayQuery ? '?'.http_build_query($displayQuery) : '');
        $catalogPreview = $category?->products()->published()->whereNotNull('image')
            ->where('image', '!=', '')->orderByDesc('id')->first();

        return view('shop.index', [
            'products' => $products,
            'category' => $category, 'brands' => $brands,
            'categories' => Category::withCount(['products' => fn ($q) => $q->published()])->get(), 'filters' => $filters,
            'activeFilters' => $activeFilters, 'clearFiltersUrl' => $clearFiltersUrl,
            'catalogPreview' => $catalogPreview,
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::published()->with(['brand', 'category', 'attributeValues.attribute'])->where('slug', $slug)->firstOrFail();
        $related = Product::published()->with(['brand', 'category'])->where('category_id', $product->category_id)->where('id', '!=', $product->id)->take(4)->get();

        return view('shop.show', compact('product', 'related'));
    }
}
