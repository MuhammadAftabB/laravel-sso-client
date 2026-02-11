<?php

namespace Liqwiz\LaravelSsoClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessGate
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('sso-client.gate', []);
        if (! ($config['enabled'] ?? true)) {
            return $next($request);
        }

        $user = Auth::user();
        if (! $user) {
            return $this->deny($request, $config);
        }

        if (isset($config['custom_callback']) && is_callable($config['custom_callback'])) {
            if (! $config['custom_callback']($user)) {
                return $this->deny($request, $config);
            }
            return $next($request);
        }

        $hasRoleOrPermission = $this->userHasRoleOrPermission($user, $config);
        if (! $hasRoleOrPermission) {
            return $this->deny($request, $config);
        }

        return $next($request);
    }

    protected function userHasRoleOrPermission($user, array $config): bool
    {
        $denyIfNoRole = $config['deny_if_no_role'] ?? true;
        $requiredRoles = $config['required_roles'] ?? [];
        $requiredPermissions = $config['required_permissions'] ?? [];

        if ($this->hasSpatiePermission()) {
            if (! empty($requiredRoles)) {
                if (! method_exists($user, 'hasAnyRole')) {
                    return false;
                }
                if (! $user->hasAnyRole($requiredRoles)) {
                    return false;
                }
            }
            if (! empty($requiredPermissions)) {
                if (! method_exists($user, 'hasAnyPermission')) {
                    return $denyIfNoRole ? $this->userHasAnyRole($user) : true;
                }
                if (! $user->hasAnyPermission($requiredPermissions)) {
                    return false;
                }
            }
            if ($denyIfNoRole && empty($requiredRoles) && empty($requiredPermissions)) {
                return $this->userHasAnyRole($user);
            }
            return true;
        }

        if ($denyIfNoRole) {
            return $this->userHasRoleBoolean($user);
        }
        return true;
    }

    protected function hasSpatiePermission(): bool
    {
        return class_exists(\Spatie\Permission\Traits\HasRoles::class);
    }

    protected function userHasAnyRole($user): bool
    {
        if (! method_exists($user, 'roles')) {
            return (bool) ($user->has_role ?? false);
        }
        return $user->roles()->exists();
    }

    protected function userHasRoleBoolean($user): bool
    {
        if (method_exists($user, 'roles') && $user->roles()->exists()) {
            return true;
        }
        return (bool) ($user->has_role ?? false);
    }

    protected function deny(Request $request, array $config): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = $config['deny_message'] ?? 'You do not have access to this application.';
        $redirect = $config['deny_redirect'] ?? '/login';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect($redirect)->with('error', $message);
    }
}
