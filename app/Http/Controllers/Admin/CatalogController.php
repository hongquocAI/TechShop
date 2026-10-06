<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Quản lý Danh mục, Thương hiệu, Thuộc tính (cấu hình PIM) trong một controller cho gọn */
class CatalogController extends Controller
{
    public function index()
    {
        return view('admin.catalog', [
            'categories' => Category::withCount('products')->with('attributes')->get(),
            'brands' => Brand::withCount('products')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $d = $request->validate(['name' => 'required|max:100|unique:categories,name']);
        Category::create($d + ['slug' => Str::slug($d['name'])]);

        return back()->with('success', __('Đã thêm danh mục.'));
    }

    public function destroyCategory(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', __('Danh mục còn sản phẩm, không thể xóa.'));
        }
        $category->delete();

        return back()->with('success', __('Đã xóa danh mục.'));
    }

    public function storeBrand(Request $request)
    {
        $d = $request->validate(['name' => 'required|max:100|unique:brands,name']);
        Brand::create($d + ['slug' => Str::slug($d['name'])]);

        return back()->with('success', __('Đã thêm thương hiệu.'));
    }

    public function destroyBrand(Brand $brand)
    {
        $brand->delete();

        return back()->with('success', __('Đã xóa thương hiệu.'));
    }

    public function storeAttribute(Request $request)
    {
        $d = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:100',
            'type' => 'required|in:text,number,select',
            'options' => 'nullable|string', // các lựa chọn cách nhau bởi dấu phẩy
        ]);
        $code = Str::slug($d['name'], '_');
        if (Attribute::where('category_id', $d['category_id'])->where('code', $code)->exists()) {
            return back()->with('error', __('Thuộc tính này đã tồn tại trong danh mục.'));
        }
        Attribute::create([
            'category_id' => $d['category_id'], 'name' => $d['name'], 'code' => $code, 'type' => $d['type'],
            'options' => $d['type'] === 'select' ? array_values(array_filter(array_map('trim', explode(',', $d['options'] ?? '')))) : null,
            'is_required' => $request->boolean('is_required'), 'is_filterable' => $request->boolean('is_filterable'),
        ]);

        return back()->with('success', __('Đã thêm thuộc tính.'));
    }

    public function destroyAttribute(Attribute $attribute)
    {
        $attribute->delete();

        return back()->with('success', __('Đã xóa thuộc tính.'));
    }
}
