<?php

namespace Liqwiz\LaravelSsoClient\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Liqwiz\LaravelSsoClient\Tests\Stubs\User;
use Liqwiz\LaravelSsoClient\Tests\TestCase;

class AccessGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_access_gate_denies_user_with_no_role_by_default(): void
    {
        config(['sso-client.gate.enabled' => true]);
        config(['sso-client.gate.deny_if_no_role' => true]);

        Route::get('/login', fn () => 'login')->name('login');
        Route::get('/protected', fn () => 'ok')
            ->middleware(['web', 'auth', 'sso.access_gate']);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/protected');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_access_gate_allows_user_with_has_role_true(): void
    {
        config(['sso-client.gate.enabled' => true]);
        config(['sso-client.gate.deny_if_no_role' => true]);

        Route::get('/login', fn () => 'login')->name('login');
        Route::get('/protected', fn () => 'ok')
            ->middleware(['web', 'auth', 'sso.access_gate']);

        $user = User::factory()->create();
        $user->has_role = true;
        $user->save();

        $response = $this->actingAs($user)->get('/protected');
        $response->assertOk();
        $response->assertSee('ok');
    }
}
