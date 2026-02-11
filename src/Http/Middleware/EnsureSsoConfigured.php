<?php

namespace Liqwiz\LaravelSsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSsoConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        $hubUrl = config('sso-client.hub_url');
        $clientId = config('sso-client.client_id');
        $clientSecret = config('sso-client.client_secret');
        $redirectUri = config('sso-client.redirect_uri');

        if (empty($hubUrl) || empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'SSO is not configured. Run: php artisan sso:install --token=YOUR_TOKEN --hub=HUB_URL',
                ], 503);
            }

            return response()->view('sso-client::not-configured', [], 503);
        }

        return $next($request);
    }
}
