<?php

namespace Tests\Feature;

use App\Http\Middleware\RedirectMobileToSpa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileSpaRedirectTest extends TestCase
{
    use RefreshDatabase;

    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    private const ANDROID = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Mobile Safari/537.36';

    private const DESKTOP = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

    private const GOOGLEBOT_MOBILE = 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mobile-spa.enabled' => true,
            'mobile-spa.auto_redirect' => true,
        ]);
    }

    public function test_mobile_visitor_is_redirected_from_home_to_spa(): void
    {
        $this->withHeader('User-Agent', self::IPHONE)
            ->get('/')
            ->assertRedirect(url('/app'))
            ->assertHeader('Vary', 'User-Agent');

        $this->withHeader('User-Agent', self::ANDROID)
            ->get('/')
            ->assertRedirect(url('/app'));
    }

    public function test_storefront_pages_map_to_matching_spa_routes(): void
    {
        $this->withHeader('User-Agent', self::ANDROID);

        $this->get('/pencarian?q=semen')->assertRedirect(url('/app/search?q=semen'));
        $this->get('/kategori')->assertRedirect(url('/app/categories'));
        $this->get('/kategori?category=besi-beton')->assertRedirect(url('/app/c/besi-beton'));
        $this->get('/detail-produk/semen-40kg')->assertRedirect(url('/app/products/semen-40kg'));
        $this->get('/login')->assertRedirect(url('/app/auth/login'));
        $this->get('/register')->assertRedirect(url('/app/auth/register'));
    }

    public function test_pages_without_spa_equivalent_are_not_redirected(): void
    {
        $response = $this->withHeader('User-Agent', self::ANDROID)->get('/blog');

        $this->assertFalse($response->isRedirect(), 'Halaman tanpa padanan SPA tidak boleh di-redirect.');
    }

    public function test_desktop_and_crawlers_are_never_redirected(): void
    {
        $this->withHeader('User-Agent', self::DESKTOP)->get('/')->assertOk();
        $this->withHeader('User-Agent', self::GOOGLEBOT_MOBILE)->get('/')->assertOk();
    }

    public function test_redirect_is_off_when_flags_are_disabled(): void
    {
        config(['mobile-spa.auto_redirect' => false]);
        $this->withHeader('User-Agent', self::IPHONE)->get('/')->assertOk();

        config(['mobile-spa.auto_redirect' => true, 'mobile-spa.enabled' => false]);
        $this->withHeader('User-Agent', self::IPHONE)->get('/')->assertOk();
    }

    public function test_web_preference_is_remembered_and_cleared_by_opening_app(): void
    {
        $this->withHeader('User-Agent', self::IPHONE)
            ->get('/?web=1')
            ->assertOk()
            ->assertCookie(RedirectMobileToSpa::PREFER_WEB_COOKIE, '1');

        $this->withHeader('User-Agent', self::IPHONE)
            ->withCookie(RedirectMobileToSpa::PREFER_WEB_COOKIE, '1')
            ->get('/')
            ->assertOk();

        $this->withHeader('User-Agent', self::IPHONE)
            ->withCookie(RedirectMobileToSpa::PREFER_WEB_COOKIE, '1')
            ->get('/app')
            ->assertOk()
            ->assertCookieExpired(RedirectMobileToSpa::PREFER_WEB_COOKIE);
    }

    public function test_json_and_non_get_requests_are_untouched(): void
    {
        $response = $this->withHeader('User-Agent', self::IPHONE)->getJson('/');
        $this->assertFalse($response->isRedirect());

        $response = $this->withHeader('User-Agent', self::IPHONE)->post('/newsletter/subscribe', []);
        $this->assertNotSame(url('/app'), $response->headers->get('Location'));
    }
}
