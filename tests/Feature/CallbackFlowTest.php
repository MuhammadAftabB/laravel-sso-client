<?php

namespace Liqwiz\LaravelSsoClient\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Liqwiz\LaravelSsoClient\Tests\TestCase;
use Liqwiz\LaravelSsoClient\Tests\Stubs\User;

class CallbackFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_callback_with_invalid_state_redirects_to_login(): void
    {
        $response = $this->get('/sso/callback?state=wrong&code=abc');
        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    public function test_callback_exchanges_code_and_creates_user(): void
    {
        $this->session(['sso_state' => 'valid-state-123']);

        Http::fake([
            '*/oauth/token' => Http::response([
                'access_token' => 'fake-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
            '*/api/sso/userinfo' => Http::response([
                'sub' => '99',
                'email' => 'sso@example.com',
                'name' => 'SSO User',
            ], 200),
        ]);

        $response = $this->get('/sso/callback?state=valid-state-123&code=auth-code');
        $response->assertRedirect();
        $this->assertAuthenticated();
        $user = auth()->user();
        $this->assertSame('sso@example.com', $user->email);
        $this->assertSame('SSO User', $user->name);
    }
}
