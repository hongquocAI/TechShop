<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Quản trị viên', 'email' => 'admin@techshop.test', 'password' => 'password', 'role' => 'admin']);
        User::create(['name' => 'Khách hàng', 'email' => 'khach@techshop.test', 'password' => 'password', 'phone' => '0900000000']);

        $brands = collect(['Anker', 'Logitech', 'Sony', 'Baseus', 'Xiaomi'])
            ->mapWithKeys(fn ($n) => [$n => Brand::create(['name' => $n, 'slug' => Str::slug($n)])]);

        // Mỗi danh mục có bộ thuộc tính riêng => ví dụ cho PIM / EAV
        $defs = [
            'Tai nghe' => [
                ['Kết nối', 'select', ['Bluetooth 5.0', 'Bluetooth 5.3', 'Có dây'], true, true],
                ['Thời lượng pin (giờ)', 'number', null, true, false],
                ['Chống ồn', 'select', ['Có', 'Không'], false, true],
            ],
            'Sạc & cáp' => [
                ['Công suất (W)', 'number', null, true, true],
                ['Cổng kết nối', 'select', ['USB-C', 'Lightning', 'Micro USB'], true, true],
                ['Chiều dài', 'text', null, false, false],
            ],
            'Chuột & bàn phím' => [
                ['Kết nối', 'select', ['USB', 'Bluetooth', 'Không dây 2.4G'], true, true],
                ['Màu sắc', 'text', null, false, false],
            ],
            'Pin dự phòng' => [
                ['Dung lượng (mAh)', 'number', null, true, true],
                ['Số cổng ra', 'number', null, false, false],
            ],
        ];
        $cats = [];
        foreach ($defs as $name => $attrs) {
            $cats[$name] = $c = Category::create(['name' => $name, 'slug' => Str::slug($name)]);
            foreach ($attrs as [$an, $type, $opts, $req, $filter]) {
                Attribute::create(['category_id' => $c->id, 'name' => $an, 'code' => Str::slug($an, '_'), 'type' => $type,
                    'options' => $opts, 'is_required' => $req, 'is_filterable' => $filter]);
            }
        }

        // [danh mục, tên, hãng, giá, giá KM, tồn, [giá trị theo thứ tự thuộc tính]]
        $products = [
            ['Tai nghe', 'Sony WH-CH520 Bluetooth', 'Sony', 1290000, 990000, 30, ['Bluetooth 5.3', '50', 'Không']],
            ['Tai nghe', 'Anker Soundcore Q30 chống ồn', 'Anker', 1990000, null, 15, ['Bluetooth 5.0', '40', 'Có']],
            ['Tai nghe', 'Baseus Bowie WM02', 'Baseus', 590000, 450000, 50, ['Bluetooth 5.3', '25', 'Không']],
            ['Tai nghe', 'Xiaomi Earphones Type-C', 'Xiaomi', 190000, null, 100, ['Có dây', '0', 'Không']],
            ['Sạc & cáp', 'Củ sạc Anker 735 GaNPrime 65W', 'Anker', 1090000, 890000, 40, ['65', 'USB-C', '']],
            ['Sạc & cáp', 'Cáp Baseus USB-C 100W 2m', 'Baseus', 250000, null, 120, ['100', 'USB-C', '2m']],
            ['Sạc & cáp', 'Cáp Lightning Anker 1m', 'Anker', 320000, 280000, 80, ['20', 'Lightning', '1m']],
            ['Chuột & bàn phím', 'Chuột Logitech MX Master 3S', 'Logitech', 2490000, 2190000, 12, ['Bluetooth', 'Xám']],
            ['Chuột & bàn phím', 'Bàn phím Logitech K380', 'Logitech', 790000, null, 25, ['Bluetooth', 'Trắng']],
            ['Chuột & bàn phím', 'Chuột Logitech M331 Silent', 'Logitech', 390000, null, 3, ['Không dây 2.4G', 'Đen']],
            ['Pin dự phòng', 'Pin Anker 10000mAh PowerCore', 'Anker', 690000, 590000, 45, ['10000', '2']],
            ['Pin dự phòng', 'Pin Xiaomi 20000mAh 50W', 'Xiaomi', 990000, null, 20, ['20000', '3']],
        ];

        foreach ($products as $i => [$cat, $name, $brand, $price, $sale, $stock, $vals]) {
            $p = Product::create([
                'category_id' => $cats[$cat]->id, 'brand_id' => $brands[$brand]->id, 'name' => $name,
                'slug' => Str::slug($name), 'sku' => 'PK'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'price' => $price, 'sale_price' => $sale, 'stock' => $stock, 'status' => 'published',
                'description' => "{$name} - phụ kiện công nghệ chính hãng, bảo hành 12 tháng, đổi trả trong 7 ngày.",
            ]);
            foreach ($cats[$cat]->attributes()->orderBy('id')->get() as $k => $attr) {
                if (($vals[$k] ?? '') !== '') $p->attributeValues()->create(['attribute_id' => $attr->id, 'value' => $vals[$k]]);
            }
            $p->update(['completeness' => $p->calculateCompleteness()]);
        }

        Product::create(['category_id' => $cats['Tai nghe']->id, 'name' => 'Tai nghe mẫu (nháp, thiếu dữ liệu)',
            'slug' => 'tai-nghe-mau', 'sku' => 'PK9999', 'price' => 500000, 'stock' => 10, 'status' => 'draft', 'completeness' => 20]);
    }
}
