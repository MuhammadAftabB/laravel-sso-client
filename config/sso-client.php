<?php

return [

    'hub_url' => env('SSO_HUB_URL', ''),
    'client_id' => env('SSO_CLIENT_ID', ''),
    'client_secret' => env('SSO_CLIENT_SECRET', ''),
    'redirect_uri' => env('SSO_REDIRECT_URI', ''),

    'routes' => [
        'enabled' => true,
        'prefix' => 'sso',
        'middleware' => [],
    ],

    'user' => [
        'model' => env('SSO_USER_MODEL', config('auth.providers.users.model', App\Models\User::class)),
        'hub_user_id_column' => 'hub_user_id',
        'hub_email_column' => 'hub_email',
        'hub_last_synced_at_column' => 'hub_last_synced_at',
    ],

    'store_access_token_in_session' => env('SSO_STORE_ACCESS_TOKEN', false),

    'gate' => [
        'enabled' => true,
        'deny_if_no_role' => true,
        'required_roles' => [],
        'required_permissions' => [],
        'custom_callback' => null,
        'deny_message' => 'You do not have access to this application.',
        'deny_redirect' => null,
    ],

];
