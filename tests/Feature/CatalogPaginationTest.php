<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_catalogue_splits_products_without_duplicates_when_dates_tie(): void
    {
        $first = $this->get('/san-pham')->assertOk()->assertSee('Trang 1 / 2')
            ->assertSee('aria-current="page"', false)->viewData('products');
        $second = $this->get('/san-pham?page=2')->assertOk()->assertSee('Trang 2 / 2')->viewData('products');
        $this->assertCount(6, $first);
        $this->assertCount(6, $second);
        $this->assertSame([], array_intersect($first->pluck('id')->all(), $second->pluck('id')->all()));
        $this->assertEqualsCanonicalizing(Product::published()->pluck('id')->all(),
            array_merge($first->pluck('id')->all(), $second->pluck('id')->all()));
    }

    public function test_page_size_is_bounded_and_single_page_remains_visible(): void
    {
        foreach ([12 => 12, 24 => 24, 9999 => 6, 0 => 6] as $requested => $expected) {
            $products = $this->get('/san-pham?per_page='.$requested)->assertOk()->viewData('products');
            $this->assertSame($expected, $products->perPage());
        }
        $this->get('/danh-muc/tai-nghe')->assertOk()->assertSee('Trang 1 / 1')
            ->assertSee('aria-disabled="true"', false);
        $this->get('/san-pham?q=no-matching-products')->assertOk()->assertDontSee('pagination-nav', false);
    }

    public function test_page_links_keep_search_attribute_filters_and_price_order(): void
    {
        $category = Category::where('slug', 'tai-nghe')->first();
        $attribute = $category->attributes()->where('name', 'Kết nối')->first();
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'category_id' => $category->id, 'name' => 'Paging product '.$i,
                'slug' => 'paging-'.$i, 'sku' => 'PAGING'.$i,
                'price' => 100 + $i * 50, 'stock' => 2, 'status' => 'published',
            ]);
            $product->attributeValues()->create(['attribute_id' => $attribute->id, 'value' => 'Có dây']);
        }
        $query = ['q' => 'Paging', 'min' => 100, 'max' => 1000, 'sort' => 'price_asc',
            'per_page' => 6, 'attr' => [$attribute->id => 'Có dây']];
        $response = $this->get('/danh-muc/tai-nghe?'.http_build_query($query))->assertOk();
        $first = $response->viewData('products');
        $this->assertSame(10, $first->total());
        parse_str(parse_url($first->nextPageUrl(), PHP_URL_QUERY), $nextQuery);
        $this->assertEquals($query + ['page' => 2], $nextQuery);
        $secondResponse = $this->get($first->nextPageUrl())->assertOk();
        $second = $secondResponse->viewData('products');
        $this->assertCount(4, $second);
        $this->assertSame([450, 500, 550, 600], $second->pluck('price')->all());

        // Removing one selected attribute keeps the other filters and restarts pagination.
        $chips = $secondResponse->viewData('activeFilters');
        $attributeChip = collect($chips)->first(fn ($chip) => str_starts_with($chip['label'], 'Kết nối:'));
        $this->assertSame('Kết nối: Có dây', $attributeChip['label']);
        parse_str(parse_url($attributeChip['url'], PHP_URL_QUERY), $chipQuery);
        $this->assertEquals(collect($query)->except('attr')->all(), $chipQuery);
        $this->assertSame('/danh-muc/tai-nghe', parse_url($attributeChip['url'], PHP_URL_PATH));
        $this->get($attributeChip['url'])->assertOk();

        parse_str(parse_url($secondResponse->viewData('clearFiltersUrl'), PHP_URL_QUERY), $clearQuery);
        $this->assertEquals(['sort'=>'price_asc', 'per_page'=>6], $clearQuery);
    }

    public function test_stale_page_redirects_to_last_page_with_filters_and_labels_remain_vietnamese(): void
    {
        $this->get('/san-pham?sort=price_asc&per_page=6&page=999')
            ->assertRedirect(url('/san-pham').'?sort=price_asc&per_page=6&page=2');
        $this->withCookie('techshop_locale', 'en')->get('/san-pham')
            ->assertOk()->assertSee('Trang 1 / 2')->assertSee('aria-label="Trang sau"', false)
            ->assertSee('Mỗi trang');
    }

    public function test_new_categories_appear_and_use_neutral_images_until_a_photo_is_uploaded(): void
    {
        $category = Category::create(['name'=>'Lót chuột', 'slug'=>'lot-chuot']);
        $this->get('/danh-muc/lot-chuot')->assertOk()->assertSee('Lót chuột')
            ->assertSee('0 sản phẩm')->assertSee('catalog-neutral-art', false);

        $product = Product::create([
            'category_id'=>$category->id, 'name'=>'Lót Chuột Gaming',
            'slug'=>'lot-chuot-gaming', 'sku'=>'NEW-MOUSEPAD',
            'price'=>100000, 'stock'=>5, 'status'=>'published',
        ]);
        $attribute = $category->attributes()->create([
            'name'=>'Chất liệu', 'code'=>'chat_lieu', 'type'=>'select',
            'options'=>['Vải', 'Nhựa'], 'is_filterable'=>true,
        ]);
        $product->attributeValues()->create(['attribute_id'=>$attribute->id, 'value'=>'Vải']);

        $response = $this->get('/danh-muc/lot-chuot?'.http_build_query(['attr'=>[$attribute->id=>'Vải']]))
            ->assertOk()->assertSee('1 sản phẩm')->assertSee('Chất liệu: Vải')
            ->assertSee('Chưa có ảnh')->assertDontSee('#headphones', false)->assertDontSee('#mouse', false);
        $this->assertSame($product->id, $response->viewData('products')->first()->id);
        $this->get('/san-pham')->assertOk()->assertSee('Lót chuột')->assertSee('13 sản phẩm');
        $this->get('/')->assertOk()->assertSee('Lót chuột')->assertSee('Lót Chuột Gaming');
        $this->get('/san-pham/lot-chuot-gaming')->assertOk()->assertSee('Chưa có ảnh');
        $this->withSession(['cart'=>[$product->id=>1]])->get('/gio-hang')->assertOk()->assertSee('Chưa có ảnh');
    }

    public function test_category_banner_uses_a_published_product_photo_when_available(): void
    {
        $category = Category::create(['name'=>'Lót chuột', 'slug'=>'lot-chuot']);
        $product = Product::create([
            'category_id'=>$category->id, 'name'=>'Lót Chuột Gaming',
            'slug'=>'lot-chuot-gaming', 'sku'=>'PHOTO-MOUSEPAD',
            'price'=>100000, 'stock'=>5, 'status'=>'published', 'image'=>'products/mousepad.jpg',
        ]);
        Product::create([
            'category_id'=>$category->id, 'name'=>'Draft mousepad',
            'slug'=>'draft-mousepad', 'sku'=>'DRAFT-MOUSEPAD',
            'price'=>100000, 'stock'=>5, 'status'=>'draft', 'image'=>'products/draft.jpg',
        ]);

        $response = $this->get('/danh-muc/lot-chuot')->assertOk()
            ->assertSee('catalog-category-photo', false)->assertSee('products/mousepad.jpg', false)
            ->assertDontSee('products/draft.jpg', false)->assertDontSee('catalog-neutral-art', false)
            ->assertDontSee('Chưa có ảnh');
        $this->assertSame($product->id, $response->viewData('catalogPreview')->id);
    }

    public function test_category_strip_counts_only_published_products(): void
    {
        $category = Category::where('slug', 'tai-nghe')->firstOrFail();
        Product::create([
            'category_id' => $category->id, 'name' => 'Unpublished headphones',
            'slug' => 'unpublished-headphones', 'sku' => 'DRAFT-HEADPHONES',
            'price' => 100, 'stock' => 1, 'status' => 'draft',
        ]);

        $response = $this->get('/san-pham')->assertOk()
            ->assertSee('Chọn danh mục sản phẩm')->assertSee('catalog-intro', false);
        $this->assertSame(4, $response->viewData('categories')->firstWhere('id', $category->id)->products_count);
        $this->assertSame(12, $response->viewData('categories')->sum('products_count'));
        $this->assertCount(0, $response->viewData('activeFilters'));
        $response->assertDontSee('catalog-filter-chip', false);
    }
}
