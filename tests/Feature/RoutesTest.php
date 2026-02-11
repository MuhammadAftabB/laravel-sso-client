<?php

namespace Liqwiz\LaravelSsoClient\Tests\Feature;

use Liqwiz\LaravelSsoClient\Tests\TestCase;

class RoutesTest extends TestCase
{
    public function test_sso_login_route_exists(): void
    {
        $response = $this->get('/sso/login');
        $response->assertRedirect();
        $this->assertStringContainsString('oauth/authorize', $response->headers->get('Location'));
        $this->assertStringContainsString('client_id=test-client-id', $response->headers->get('Location'));
    }

    public function test_sso_callback_route_exists(): void
    {
        $response = $this->get('/sso/callback');
        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }

    public function test_sso_logout_route_exists(): void
    {
        $response = $this->post('/sso/logout');
        $response->assertRedirect('/');
    }
}
