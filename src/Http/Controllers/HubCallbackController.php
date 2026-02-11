<?php

namespace Liqwiz\LaravelSsoClient\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Liqwiz\LaravelSsoClient\Resolvers\UserResolver;

class HubCallbackController
{
    public function __invoke(Request $request, Guard $guard, UserResolver $resolver): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect('/login')->with('error', $request->input('error_description', $request->input('error', 'Authorization failed.')));
        }

        $state = $request->session()->pull('sso_state');
        if (! $state || $state !== $request->input('state')) {
            return redirect('/login')->with('error', 'Invalid state. Please try again.');
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect('/login')->with('error', 'Authorization code missing.');
        }

        $hubUrl = rtrim(config('sso-client.hub_url'), '/');
        $tokenUrl = $hubUrl.'/oauth/token';
        $userinfoUrl = $hubUrl.'/api/sso/userinfo';

        $tokenResponse = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'client_id' => config('sso-client.client_id'),
            'client_secret' => config('sso-client.client_secret'),
            'redirect_uri' => config('sso-client.redirect_uri'),
            'code' => $code,
        ]);

        if (! $tokenResponse->successful()) {
            return redirect('/login')->with('error', 'Token exchange failed. Please try again.');
        }

        $tokenData = $tokenResponse->json();
        $accessToken = $tokenData['access_token'] ?? null;
        if (! $accessToken) {
            return redirect('/login')->with('error', 'Invalid token response.');
        }

        $userinfoResponse = Http::withToken($accessToken)->get($userinfoUrl);
        if (! $userinfoResponse->successful()) {
            return redirect('/login')->with('error', 'Could not fetch user info.');
        }

        $userInfo = $userinfoResponse->json();
        $user = $resolver->resolve($userInfo);

        if (! $user) {
            return redirect('/login')->with('error', 'Could not resolve local user.');
        }

        $guard->login($user, true);

        if (config('sso-client.store_access_token_in_session', false)) {
            $request->session()->put('sso_access_token', $accessToken);
        }

        $intended = $request->session()->pull('url.intended', config('app.url'));

        return redirect()->intended($intended);
    }
}
