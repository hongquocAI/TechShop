<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\SemanticCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['semantic-search.directory' => base_path('work/semantic-tests/'.bin2hex(random_bytes(6)))]);
        Http::preventStrayRequests();
    }

    public function test_disabled_feature_keeps_keywords_and_does_not_call_service(): void
    {
        $this->get('/san-pham?q=Sony')->assertOk()->assertSee('Sony WH-CH520 Bluetooth');
        Http::assertNothingSent();
    }

    public function test_semantic_matches_are_ranked_and_keyword_matches_have_priority(): void
    {
        config(['semantic-search.enabled' => true]);
        $sony = Product::where('slug', 'sony-wh-ch520-bluetooth')->first();
        $anker = Product::where('slug', 'anker-soundcore-q30-chong-on')->first();
        Http::fake(['*/search' => Http::response(['ids' => [$anker->id, $sony->id]])]);
        $products = $this->get('/san-pham?q=Sony')->assertOk()->assertSee('Phù hợp nhất')->viewData('products');
        $this->assertSame([$sony->id, $anker->id], $products->pluck('id')->all());
        Http::assertSent(fn ($request) => $request['query'] === 'Sony' && $request['limit'] === 100);
    }

    public function test_database_filters_and_visibility_still_apply_to_semantic_ids(): void
    {
        config(['semantic-search.enabled' => true]);
        $sony = Product::where('slug', 'sony-wh-ch520-bluetooth')->first();
        $anker = Product::where('slug', 'anker-soundcore-q30-chong-on')->first();
        $draft = Product::create(['category_id' => $sony->category_id, 'name' => 'Hidden test',
            'slug' => 'hidden-test', 'sku' => 'HIDDEN', 'price' => 100, 'stock' => 1, 'status' => 'draft']);
        Http::fake(['*/search' => Http::response(['ids' => [$draft->id, $anker->id, $sony->id, 99999]])]);
        $products = $this->get('/danh-muc/tai-nghe?q=nghe+nhac&max=1500000')->assertOk()->viewData('products');
        $this->assertSame([$sony->id], $products->pluck('id')->all());
        Http::assertSent(fn ($request) => $request['filters']['category_id'] === $sony->category_id
            && $request['filters']['max'] === '1500000');
        $this->get('/san-pham?q=nghe+nhac&sort=price_asc')->assertOk()->assertViewHas('products',
            fn ($products) => $products->pluck('id')->all() === [$sony->id, $anker->id]);
    }

    public function test_empty_semantic_results_remain_empty_and_generate_valid_sql(): void
    {
        config(['semantic-search.enabled' => true]);
        Http::fake(['*/search' => Http::response(['ids' => []])]);
        $this->get('/san-pham?q=nonexistent-product')->assertOk()->assertSee('Chưa tìm thấy món phù hợp');
    }

    public function test_semantic_pagination_keeps_ranking_without_duplicate_products(): void
    {
        config(['semantic-search.enabled' => true]);
        $ids = Product::published()->orderBy('id')->pluck('id')->reverse()->values()->all();
        Http::fake(['*/search' => Http::response(['ids' => $ids])]);
        $first = $this->get('/san-pham?q=semantic-only&per_page=6')->assertOk()->viewData('products');
        $second = $this->get($first->nextPageUrl())->assertOk()->viewData('products');
        $this->assertSame($ids, array_merge($first->pluck('id')->all(), $second->pluck('id')->all()));
        $this->assertSame([], array_intersect($first->pluck('id')->all(), $second->pluck('id')->all()));
        $this->assertStringContainsString('q=semantic-only', $first->nextPageUrl());
    }

    public function test_timeout_falls_back_and_circuit_breaker_skips_repeated_calls(): void
    {
        config(['semantic-search.enabled' => true]);
        Http::fake(fn () => throw new ConnectionException('Service stopped'));
        $this->get('/san-pham?q=Sony')->assertOk()->assertSee('Sony WH-CH520 Bluetooth');
        $this->assertTrue(Cache::has('semantic-search:unavailable'));
        Http::fake(fn () => throw new \LogicException('Circuit breaker must not send another request'));
        $this->get('/san-pham?q=Logitech')->assertOk()->assertSee('Logitech');
        Http::assertNothingSent();
    }

    public function test_malformed_service_response_uses_keyword_fallback(): void
    {
        config(['semantic-search.enabled' => true]);
        Http::fake(['*/search' => Http::response(['ids' => ['1 OR 1=1']])]);
        $this->get('/san-pham?q=Sony')->assertOk()->assertSee('Sony WH-CH520 Bluetooth');
    }

    public function test_export_and_product_changes_exclude_unpublished_products(): void
    {
        $catalog = app(SemanticCatalog::class);
        $this->assertSame(Product::published()->count(), $catalog->export());
        $export = json_decode(file_get_contents(config('semantic-search.directory').'/catalog.json'), true);
        $this->assertCount(Product::published()->count(), $export['products']);
        config(['semantic-search.enabled' => true]);
        $product = Product::where('slug', 'sony-wh-ch520-bluetooth')->first();
        $product->update(['name' => 'Tai nghe đổi tên']);
        $catalog->flush();
        $path = config('semantic-search.directory').'/changes/'.$product->id.'.json';
        $record = json_decode(file_get_contents($path), true);
        $this->assertStringContainsString('Tai nghe đổi tên', $record['text']);
        $this->assertNotEmpty($record['attributes']);
        $product->update(['status' => 'draft']);
        $catalog->flush();
        $this->assertTrue(json_decode(file_get_contents($path), true)['deleted']);
    }
}
