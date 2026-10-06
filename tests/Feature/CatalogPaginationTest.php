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
        $second = $this->get($first->nextPageUrl())->assertOk()->viewData('products');
        $this->assertCount(4, $second);
        $this->assertSame([450, 500, 550, 600], $second->pluck('price')->all());
    }

    public function test_stale_page_redirects_to_last_page_with_filters_and_labels_remain_vietnamese(): void
    {
        $this->get('/san-pham?sort=price_asc&per_page=6&page=999')
            ->assertRedirect(url('/san-pham').'?sort=price_asc&per_page=6&page=2');
        $this->withCookie('techshop_locale', 'en')->get('/san-pham')
            ->assertOk()->assertSee('Trang 1 / 2')->assertSee('aria-label="Trang sau"', false)
            ->assertSee('Mỗi trang');
    }
}
