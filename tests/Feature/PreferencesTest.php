<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_storefront_is_vietnamese_and_has_only_a_light_dark_switch(): void
    {
        $this->get('/')->assertOk()->assertSee('lang="vi"', false)
            ->assertSee('Mua theo danh mục')->assertSee('role="switch"', false)
            ->assertSee('aria-label="Giao diện tối"', false)
            ->assertDontSee('preferred-language', false)->assertDontSee('English')
            ->assertDontSee('data-theme-choice', false)->assertDontSee('Theo hệ thống');
    }

    public function test_old_english_cookie_cannot_change_storefront_or_admin_language(): void
    {
        $this->withCookie('techshop_locale', 'en');
        $this->get('/danh-muc/tai-nghe')->assertOk()->assertSee('lang="vi"', false)
            ->assertSee('Bộ lọc sản phẩm')->assertDontSee('Product filters');
        $this->get('/san-pham/sony-wh-ch520-bluetooth')->assertOk()->assertSee('Thông số kỹ thuật');
        $this->get('/dang-nhap')->assertOk()->assertSee('Ghi nhớ đăng nhập');
        $this->actingAs(User::where('role', 'admin')->first());
        $this->get('/admin')->assertOk()->assertSee('lang="vi"', false)
            ->assertSee('Doanh thu (đã thanh toán)')->assertSee('role="switch"', false)
            ->assertDontSee('preferred-language', false);
        $this->get('/admin/products/create')->assertOk()->assertSee('Tên sản phẩm');
    }

    public function test_language_switch_endpoint_is_removed(): void
    {
        $this->post('/preferences/locale', ['locale' => 'en'])->assertNotFound();
    }
}
