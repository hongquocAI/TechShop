<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SemanticSearch;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        return view('shop.home', [
            'featured' => Product::published()->with('brand')->where('stock', '>', 0)->latest()->take(8)->get(),
            'categories' => Category::withCount(['products' => fn ($q) => $q->published()])->get(),
        ]);
    }

    public function index(Request $request, ?Category $category = null)
    {
        $q = Product::published()->with(['brand', 'category']);

        if ($category) $q->where('category_id', $category->id);
        $search = trim((string) $request->query('q', ''));
        $semanticIds = $search !== '' ? app(SemanticSearch::class)->search($search, [
            'category_id' => $category?->id,
            'brand_id' => $request->query('brand'),
            'min' => $request->query('min'), 'max' => $request->query('max'),
            'attributes' => (array) $request->query('attr', []),
        ]) : null;
        if ($search !== '') {
            $q->where(function ($matches) use ($search, $semanticIds) {
                $matches->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                if ($semanticIds) $matches->orWhereIn('id', $semanticIds);
            });
        }
        if ($b = $request->query('brand')) $q->where('brand_id', $b);
        if ($min = $request->query('min')) $q->whereRaw('COALESCE(sale_price, price) >= ?', [(int) $min]);
        if ($max = $request->query('max')) $q->whereRaw('COALESCE(sale_price, price) <= ?', [(int) $max]);

        // Lọc theo thuộc tính động (is_filterable): ?attr[5]=Bluetooth 5.3
        foreach ((array) $request->query('attr', []) as $attrId => $val) {
            if ($val !== '' && $val !== null) {
                $q->whereHas('attributeValues', fn ($v) => $v->where('attribute_id', $attrId)->where('value', $val));
            }
        }

        $rankOrder = $semanticIds
            ? 'CASE id '.implode(' ', array_map(
                fn ($id, $rank) => 'WHEN '.(int) $id.' THEN '.(int) $rank,
                $semanticIds, array_keys($semanticIds),
            )).' ELSE 1000 END'
            : 'id DESC';
        match ($request->query('sort')) {
            'price_asc' => $q->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $q->orderByRaw('COALESCE(sale_price, price) desc'),
            default => $semanticIds !== null && $search !== ''
                ? $q->orderByRaw('CASE WHEN name LIKE ? OR sku LIKE ? THEN 0 ELSE 1 END', ["%{$search}%", "%{$search}%"])
                    ->orderByRaw($rankOrder)
                : $q->latest(),
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

        return view('shop.index', [
            'products' => $products,
            'category' => $category, 'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::all(), 'filters' => $filters,
            'semanticSearch' => $semanticIds !== null && $search !== '',
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::published()->with(['brand', 'category', 'attributeValues.attribute'])->where('slug', $slug)->firstOrFail();
        $related = Product::published()->with('brand')->where('category_id', $product->category_id)->where('id', '!=', $product->id)->take(4)->get();

        return view('shop.show', compact('product', 'related'));
    }
}
