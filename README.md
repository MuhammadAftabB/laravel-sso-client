# Laravel SSO Client

Laravel package that turns your app into an SSO client. Authenticate users against a central **SSO Hub** (OAuth2 IdP) and keep authorization (roles/permissions) local.

**Repository:** [github.com/MuhammadAftabB/laravel-sso-client](https://github.com/MuhammadAftabB/laravel-sso-client)

---

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12
- An SSO Hub that implements the [Hub API](#hub-api) (install token + register-client + userinfo)

---

## Installation

### 1. Install the package

**From Packagist (after the package is published):**

```bash
composer require muhammadaftab/laravel-sso-client
```

**From GitHub (development):**

```bash
composer require muhammadaftab/laravel-sso-client dev-main
```

Or add to `composer.json` and run `composer update`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/MuhammadAftabB/laravel-sso-client"
        }
    ],
    "require": {
        "muhammadaftab/laravel-sso-client": "dev-main"
    }
}
```

### 2. One-command setup

Get a one-time **install token** from your Hub (e.g. Hub UI → SSO Install Tokens → Generate). Then run:

```bash
php artisan sso:install --token=YOUR_INSTALL_TOKEN --hub=https://hub.example.com
```

This will:

- Register this app with the Hub
- Write `SSO_HUB_URL`, `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET`, `SSO_REDIRECT_URI` to `.env`
- Publish `config/sso-client.php` if not present

### 3. Optional: user columns for Hub linking

To link local users to Hub identities (recommended):

```bash
php artisan vendor:publish --tag=sso-client-migrations
php artisan migrate
```

Add to your User model `$fillable`: `hub_user_id`, `hub_email`, `hub_last_synced_at` (if you use the migration).

### 4. Add “Login with Hub” to your app

In your login view:

```html
<a href="{{ url('/sso/login') }}">Login with Hub</a>
```

Protected routes: use `auth` middleware as usual. To **deny users with no local role** after SSO login, add the Access Gate middleware (see [Access Gate](#access-gate)).

---

## Configuration

### `config/sso-client.php`

| Key                         | Env                 | Description                                        |
| --------------------------- | ------------------- | -------------------------------------------------- |
| `hub_url`                   | `SSO_HUB_URL`       | Hub base URL                                       |
| `client_id`                 | `SSO_CLIENT_ID`     | OAuth client ID from Hub                           |
| `client_secret`             | `SSO_CLIENT_SECRET` | OAuth client secret                                |
| `redirect_uri`              | `SSO_REDIRECT_URI`  | Callback URL (e.g. `APP_URL/sso/callback`)         |
| `routes.prefix`             | -                   | Route prefix (default: `sso`)                      |
| `user.model`                | -                   | Your User model class                              |
| `gate.enabled`              | -                   | Enable Access Gate (default: true)                 |
| `gate.deny_if_no_role`      | -                   | Deny if user has no role (default: true)          |
| `gate.required_roles`       | -                   | Require at least one of these roles (Spatie)        |
| `gate.required_permissions` | -                   | Require at least one of these permissions (Spatie)  |
| `gate.custom_callback`      | -                   | Custom callable for access check                   |
| `gate.deny_message`         | -                   | Message shown when access is denied                 |
| `gate.deny_redirect`        | -                   | Redirect path when access is denied (default: `/login`) |

### Access Gate

After SSO login, the **Access Gate** can block users who have no local role/permission:

- **Default:** `deny_if_no_role` is `true` → user must have at least one role (Spatie) or a truthy `has_role` (fallback).
- With **Spatie Laravel Permission:** uses `hasAnyRole()` / `hasAnyPermission()` when `required_roles` or `required_permissions` are set.
- Without Spatie: uses a simple `has_role` attribute or config callback.

Apply the middleware to routes that should be protected by the gate:

```php
Route::middleware(['auth', 'sso.access_gate'])->group(function () {
    Route::get('/dashboard', ...);
});
```

If the user fails the gate: they are logged out, session is invalidated, and they are redirected to login with an error message (configurable via `gate.deny_message`, `gate.deny_redirect`).

---

## Hub API

The Hub must provide:

1. **POST /api/sso/install-tokens** (auth required) – returns a one-time install token.
2. **POST /api/sso/register-client** – body: `install_token`, `name`, `app_url`, `redirect_uri`; returns `client_id`, `client_secret`.
3. **GET /api/sso/userinfo** (OAuth2 Bearer) – returns `sub`, `email`, `name` (and optionally `avatar_url`, `updated_at`).

OAuth2: Authorization Code grant; authorize at `{hub}/oauth/authorize`, token at `{hub}/oauth/token`.

---

## Troubleshooting

### “SSO is not configured”

- Run `php artisan sso:install --token=... --hub=...` and ensure `.env` has `SSO_HUB_URL`, `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET`, `SSO_REDIRECT_URI`.
- Run `php artisan config:clear`.

### Redirect URI mismatch

- `SSO_REDIRECT_URI` must be exactly the callback URL the Hub uses (e.g. `https://yourapp.com/sso/callback`).
- In production the Hub typically requires `redirect_uri` to use **https** and to match the app’s origin.

### Invalid state / Session lost

- If the Hub and client run on the **same domain** (e.g. different ports on localhost), use a **unique session cookie name** in the client’s `.env` (e.g. `SESSION_COOKIE=my_client_session`) so the Hub does not overwrite the client’s session.

### invalid_client / Token exchange failed

- Confirm `SSO_CLIENT_ID` and `SSO_CLIENT_SECRET` match the Hub’s registered client.
- Ensure the client is **active** on the Hub (Registered Clients page).

### User not found / Could not resolve local user

- The Hub userinfo must include `sub` or `email`. The package resolves by `hub_user_id` then `email`, then creates a user if not found.
- If you use the migration, ensure `hub_user_id` / `hub_email` are in the User model’s `$fillable`.

### Access denied after login (no role)

- By default, users with **no local role** are denied. Assign a role (e.g. with Spatie) or set `gate.deny_if_no_role` to `false`, or use `gate.required_roles` / `gate.custom_callback`.

---

## License

MIT. See the [repository](https://github.com/MuhammadAftabB/laravel-sso-client) for details.
