<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

describe('POST /api/login', function () {
    it('returns a token for valid credentials', function () {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token']);
    });

    it('returns 401 for invalid credentials', function () {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    });

    it('issues a token that expires in one week', function () {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();

        $token = PersonalAccessToken::latest()->first();
        expect($token->expires_at)->not->toBeNull();
        expect($token->expires_at->diffInDays(now()))->toBeLessThanOrEqual(7);
    });
});
