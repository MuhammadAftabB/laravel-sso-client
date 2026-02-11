<?php

namespace Liqwiz\LaravelSsoClient\Tests\Feature;

use Liqwiz\LaravelSsoClient\Tests\TestCase;

class EnsureSsoConfiguredTest extends TestCase
{
    public function test_when_sso_not_configured_returns_503(): void
    {
        config(['sso-client.hub_url' => '']);
        config(['sso-client.client_id' => '']);
        config(['sso-client.client_secret' => '']);
        config(['sso-client.redirect_uri' => '']);
        $response = $this->get('/sso/login');
        $response->assertStatus(503);
    }
}
