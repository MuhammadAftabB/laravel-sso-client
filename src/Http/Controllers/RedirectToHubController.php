<?php

namespace Liqwiz\LaravelSsoClient\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RedirectToHubController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $hubUrl = rtrim(config('sso-client.hub_url'), '/');
        $authorizeUrl = $hubUrl.'/oauth/authorize';
        $clientId = config('sso-client.client_id');
        $redirectUri = config('sso-client.redirect_uri');

        $state = Str::random(40);
        $request->session()->put('sso_state', $state);
        $request->session()->save();

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => '*',
            'state' => $state,
        ]);

        return redirect($authorizeUrl.'?'.$query);
    }
}
