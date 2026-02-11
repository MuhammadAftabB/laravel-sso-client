<?php

namespace Liqwiz\LaravelSsoClient\Resolvers;

use Illuminate\Database\Eloquent\Model;

class UserResolver
{
    public function resolve(array $userInfo): ?Model
    {
        $modelClass = config('sso-client.user.model');
        $hubIdColumn = config('sso-client.user.hub_user_id_column', 'hub_user_id');
        $hubEmailColumn = config('sso-client.user.hub_email_column', 'hub_email');
        $hubSyncedColumn = config('sso-client.user.hub_last_synced_at_column', 'hub_last_synced_at');

        $sub = $userInfo['sub'] ?? null;
        $email = $userInfo['email'] ?? null;
        $name = $userInfo['name'] ?? '';

        if (! $sub && ! $email) {
            return null;
        }

        $user = null;
        if ($sub && \Schema::hasColumn((new $modelClass)->getTable(), $hubIdColumn)) {
            $user = $modelClass::where($hubIdColumn, $sub)->first();
        }
        if (! $user && $email) {
            $user = $modelClass::where('email', $email)->first();
        }
        if (! $user && $email) {
            $user = $this->createUser($modelClass, $name, $email, $sub, $hubIdColumn, $hubEmailColumn, $hubSyncedColumn);
        }

        if ($user && \Schema::hasColumn($user->getTable(), $hubIdColumn)) {
            $user->{$hubIdColumn} = $sub;
        }
        if ($user && \Schema::hasColumn($user->getTable(), $hubEmailColumn)) {
            $user->{$hubEmailColumn} = $email;
        }
        if ($user && \Schema::hasColumn($user->getTable(), $hubSyncedColumn)) {
            $user->{$hubSyncedColumn} = now();
        }
        if ($user) {
            $user->name = $name ?: ($user->name ?? $email);
            $user->email = $email;
            $user->save();
        }

        return $user;
    }

    protected function createUser(string $modelClass, string $name, string $email, $sub, string $hubIdColumn, string $hubEmailColumn, string $hubSyncedColumn): Model
    {
        $attrs = [
            'name' => $name ?: $email,
            'email' => $email,
        ];
        if (\Schema::hasColumn((new $modelClass)->getTable(), $hubIdColumn)) {
            $attrs[$hubIdColumn] = $sub;
        }
        if (\Schema::hasColumn((new $modelClass)->getTable(), $hubEmailColumn)) {
            $attrs[$hubEmailColumn] = $email;
        }
        if (\Schema::hasColumn((new $modelClass)->getTable(), $hubSyncedColumn)) {
            $attrs[$hubSyncedColumn] = now();
        }
        if (\Schema::hasColumn((new $modelClass)->getTable(), 'password')) {
            $attrs['password'] = bcrypt(\Illuminate\Support\Str::random(32));
        }

        return $modelClass::create($attrs);
    }
}
