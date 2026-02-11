<?php

namespace Liqwiz\LaravelSsoClient\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class HubLogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->session()->forget('sso_access_token');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
