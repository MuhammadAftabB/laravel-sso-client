<?php

namespace Liqwiz\LaravelSsoClient\Tests\Stubs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Liqwiz\LaravelSsoClient\Tests\Stubs\UserFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'users';

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_role' => 'boolean',
        ];
    }
}
