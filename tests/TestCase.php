<?php

namespace Liqwiz\LaravelSsoClient\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liqwiz\LaravelSsoClient\SsoClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [SsoClientServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(\Illuminate\Support\Str::random(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.url', 'http://client.test');
        $app['config']->set('auth.guards.web.provider', 'users');
        $app['config']->set('auth.providers.users.model', \Liqwiz\LaravelSsoClient\Tests\Stubs\User::class);
        $app['config']->set('sso-client.hub_url', 'http://hub.test');
        $app['config']->set('sso-client.client_id', 'test-client-id');
        $app['config']->set('sso-client.client_secret', 'test-client-secret');
        $app['config']->set('sso-client.redirect_uri', 'http://client.test/sso/callback');
        $app['config']->set('sso-client.user.model', \Liqwiz\LaravelSsoClient\Tests\Stubs\User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
