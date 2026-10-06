<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Nhập/xuất sản phẩm hàng loạt bằng CSV (tính năng PIM).
 * Cột: sku,name,category,brand,price,stock,status,description,attributes
 * Cột attributes có dạng: ma_thuoc_tinh=gia_tri;ma_thuoc_tinh=gia_tri
 */
class ImportExportController extends Controller
{
    private const HEADER = ['sku', 'name', 'category', 'brand', 'price', 'stock', 'status', 'description', 'attributes'];

    public function form() { return view('admin.import'); }

    public function export()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng tiếng Việt
            fputcsv($out, self::HEADER);
            Product::with(['category', 'brand', 'attributeValues.attribute'])->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $p) {
                    fputcsv($out, [$p->sku, $p->name, $p->category->name, $p->brand?->name, $p->price, $p->stock, $p->status, $p->description,
                        $p->attributeValues->map(fn ($v) => $v->attribute->code.'='.$v->value)->implode(';')]);
                }
            });
            fclose($out);
        }, 'products-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($h) => trim(ltrim($h, "\xEF\xBB\xBF")), fgetcsv($handle) ?: []);
        if (array_diff(['sku', 'name', 'category', 'price'], $header)) {
            return back()->with('error', 'File thiếu cột bắt buộc: sku, name, category, price.');
        }

        $ok = 0; $errors = []; $line = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            $r = array_combine($header, array_pad($row, count($header), null));
            if (blank($r['sku'] ?? null) || blank($r['name'] ?? null) || ! is_numeric($r['price'] ?? null)) {
                $errors[] = __('Dòng :line: thiếu sku/name hoặc giá không hợp lệ.', ['line' => $line]); continue;
            }
            $category = Category::where('name', $r['category'])->first();
            if (! $category) { $errors[] = __('Dòng :line: không có danh mục “:category”.', ['line' => $line, 'category' => $r['category']]); continue; }
            $status = in_array($r['status'] ?? '', ['draft', 'review', 'published']) ? $r['status'] : 'draft';

            $product = Product::withTrashed()->firstOrNew(['sku' => $r['sku']]);
            if ($product->trashed()) $product->restore();
            $product->fill([
                'name' => $r['name'], 'category_id' => $category->id,
                'brand_id' => filled($r['brand'] ?? null) ? Brand::firstOrCreate(['name' => $r['brand']], ['slug' => Str::slug($r['brand'])])->id : null,
                'price' => (int) $r['price'], 'stock' => (int) ($r['stock'] ?? 0), 'status' => $status, 'description' => $r['description'] ?? null,
            ]);
            if (! $product->exists) $product->slug = Str::slug($r['name']).'-'.Str::lower(Str::random(4));
            $product->save();

            foreach (array_filter(explode(';', $r['attributes'] ?? '')) as $pair) {
                [$code, $val] = array_pad(explode('=', $pair, 2), 2, '');
                if ($attr = Attribute::where('category_id', $category->id)->where('code', trim($code))->first()) {
                    $product->attributeValues()->updateOrCreate(['attribute_id' => $attr->id], ['value' => trim($val)]);
                }
            }
            $product->update(['completeness' => $product->calculateCompleteness()]);
            $ok++;
        }
        fclose($handle);

        return back()->with('success', __('Nhập thành công :count sản phẩm.', ['count' => $ok]))->with('import_errors', $errors);
    }
}
