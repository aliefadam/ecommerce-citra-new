<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileSpaFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mobile-spa.enabled' => true,
            'mobile-spa.pilot' => true,
            'mobile-spa.version' => 'foundation-test',
        ]);
    }

    public function test_mobile_spa_is_hidden_when_feature_flag_is_disabled(): void
    {
        config(['mobile-spa.enabled' => false]);

        $this->get('/app')->assertNotFound();
        $this->getJson('/api/mobile/v1/bootstrap')->assertNotFound();
    }

    public function test_mobile_spa_shell_supports_direct_nested_routes_without_changing_desktop(): void
    {
        $this->get('/app/categories')
            ->assertOk()
            ->assertSee('id="mobile-app"', false)
            ->assertSee('data-app-version="foundation-test"', false)
            ->assertSee(route('mobile-app.manifest'), false)
            ->assertSee('type="module"', false)
            ->assertDontSee('data-storefront-shell', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-storefront-shell', false)
            ->assertDontSee('data-testid="mobile-spa-shell"', false);
    }

    public function test_mobile_manifest_is_isolated_to_app_scope(): void
    {
        $this->get(route('mobile-app.manifest'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('id', '/app')
            ->assertJsonPath('start_url', '/app')
            ->assertJsonPath('scope', '/app/')
            ->assertJsonPath('display', 'standalone');

        $worker = file_get_contents(public_path('app-service-worker.js'));

        $this->assertStringContainsString("scope: '/app/'", file_get_contents(resource_path('js/mobile-app/services/pwa.js')));
        $this->assertStringContainsString("url.pathname.startsWith('/api/')", $worker);
        $this->assertStringContainsString("url.pathname.startsWith('/sanctum/')", $worker);
        $this->assertStringNotContainsString('caches.put(request', $worker);
    }

    public function test_bootstrap_exposes_only_safe_optional_session_data(): void
    {
        $this->withHeader('Origin', 'http://localhost')
            ->getJson('/api/mobile/v1/bootstrap')
            ->assertOk()
            ->assertHeader('X-Request-ID')
            ->assertJsonPath('data.app.version', 'foundation-test')
            ->assertJsonPath('data.app.pilot', true)
            ->assertJsonPath('data.user', null)
            ->assertJsonMissingPath('data.app.secret');
    }

    public function test_mobile_login_session_and_logout_use_same_origin_session_authentication(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile@example.test',
            'password' => 'mobile-secret',
            'role' => 'user',
        ]);

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/mobile/v1/auth/login', [
                'email' => 'MOBILE@example.test',
                'password' => 'mobile-secret',
                'return_url' => '/app/account',
            ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'mobile@example.test')
            ->assertJsonPath('data.return_url', '/app/account')
            ->assertJsonMissingPath('data.user.password');

        $this->assertAuthenticatedAs($user);

        $this->withHeader('Origin', 'http://localhost')
            ->getJson('/api/mobile/v1/session')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/mobile/v1/auth/logout')
            ->assertNoContent();

        // Feature tests share the auth manager across requests; a real HTTP
        // lifecycle creates a fresh guard for the next request.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Origin', 'http://localhost')
            ->getJson('/api/mobile/v1/session')
            ->assertUnauthorized();
    }

    public function test_mobile_login_rejects_invalid_credentials_and_external_return_url(): void
    {
        User::factory()->create([
            'email' => 'mobile@example.test',
            'password' => 'mobile-secret',
        ]);

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/mobile/v1/auth/login', [
                'email' => 'mobile@example.test',
                'password' => 'wrong-secret',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/mobile/v1/auth/login', [
                'email' => 'mobile@example.test',
                'password' => 'mobile-secret',
                'return_url' => 'https://attacker.example',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('return_url');
    }
}
