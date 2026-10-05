<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::with(['category', 'brand'])->latest();
        if ($s = $request->query('q')) $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%"));
        if ($st = $request->query('status')) $q->where('status', $st);

        return view('admin.products.index', ['products' => $q->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.products.form', $this->formData(new Product(['status' => 'draft'])));
    }

    public function store(Request $request)
    {
        $product = new Product;
        $this->save($product, $request);

        return redirect()->route('admin.products.index')->with('success', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $this->save($product, $request);

        return redirect()->route('admin.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product)
    {
        $product->delete(); // soft delete

        return back()->with('success', 'Đã xóa sản phẩm.');
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::with('attributes')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'values' => $product->exists ? $product->attributeValues->pluck('value', 'attribute_id') : collect(),
        ];
    }

    private function save(Product $product, Request $request): void
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|max:200',
            'sku' => ['required', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)->withoutTrashed()],
            'price' => 'required|integer|min:0',
            'sale_price' => 'nullable|integer|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,review,published',
            'attr' => 'array',
        ]);

        // Nhóm thuộc tính bắt buộc phải điền trước khi cho "Đang bán"
        $attributes = Attribute::where('category_id', $data['category_id'])->get();
        if ($data['status'] === 'published') {
            foreach ($attributes->where('is_required', true) as $a) {
                if (blank($data['attr'][$a->id] ?? null)) {
                    abort(back()->withInput()->withErrors(["attr.{$a->id}" => "Thiếu thuộc tính bắt buộc \"{$a->name}\" để xuất bản."]));
                }
            }
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']);
        }
        if (! $product->exists) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        }

        $product->fill(collect($data)->except('attr')->all())->save();

        // Lưu giá trị thuộc tính động (chỉ thuộc tính thuộc danh mục đã chọn)
        $product->attributeValues()->whereNotIn('attribute_id', $attributes->pluck('id'))->delete();
        foreach ($attributes as $a) {
            $val = trim((string) ($data['attr'][$a->id] ?? ''));
            $val === ''
                ? $product->attributeValues()->where('attribute_id', $a->id)->delete()
                : $product->attributeValues()->updateOrCreate(['attribute_id' => $a->id], ['value' => $val]);
        }

        $product->update(['completeness' => $product->fresh()->calculateCompleteness()]);
    }
}
