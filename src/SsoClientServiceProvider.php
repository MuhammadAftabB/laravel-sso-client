<?php

namespace Liqwiz\LaravelSsoClient;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SsoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sso-client.php', 'sso-client');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sso-client');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sso-client.php' => config_path('sso-client.php'),
            ], 'sso-client-config');

            $this->publishes([
                __DIR__.'/../database/migrations/add_sso_columns_to_users_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_sso_columns_to_users_table.php'),
            ], 'sso-client-migrations');

            $this->commands([
                Commands\SsoInstallCommand::class,
            ]);
        }

        $this->registerRoutes();
        $this->registerMiddleware();
    }

    protected function registerRoutes(): void
    {
        $router = $this->app->make(Router::class);
        $router->middlewareGroup('sso.client', [
            \Liqwiz\LaravelSsoClient\Http\Middleware\EnsureSsoConfigured::class,
        ]);

        $config = config('sso-client');
        if (($config['routes']['enabled'] ?? true) && ! $this->app->routesAreCached()) {
            $prefix = $config['routes']['prefix'] ?? 'sso';
            $router->group([
                'prefix' => $prefix,
                'middleware' => array_merge(['web', 'sso.client'], $config['routes']['middleware'] ?? []),
            ], function (Router $router): void {
                $router->get('login', \Liqwiz\LaravelSsoClient\Http\Controllers\RedirectToHubController::class)->name('sso.login');
                $router->get('callback', \Liqwiz\LaravelSsoClient\Http\Controllers\HubCallbackController::class)->name('sso.callback');
                $router->post('logout', \Liqwiz\LaravelSsoClient\Http\Controllers\HubLogoutController::class)->name('sso.logout');
            });
        }
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('sso.access_gate', \Liqwiz\LaravelSsoClient\Http\Middleware\AccessGate::class);
    }
}
