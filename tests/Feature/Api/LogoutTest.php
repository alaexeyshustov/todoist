<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

describe('POST /api/logout', function () {
    it('deletes the current token from the database', function () {
        $user = User::factory()->create();
        $token = $user->createToken('md-editor');

        expect(PersonalAccessToken::count())->toBe(1);

        $this->withToken($token->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk();

        expect(PersonalAccessToken::count())->toBe(0);
    });

    it('requires authentication', function () {
        $this->postJson('/api/logout')->assertUnauthorized();
    });
});
