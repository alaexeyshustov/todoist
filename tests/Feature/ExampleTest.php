<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects unauthenticated users to login', function () {
    $this->get('/')->assertRedirect('/login');
});

it('loads the app for authenticated users', function () {
    $user = User::factory()->create();

    $this->withoutVite()->actingAs($user)->get('/')->assertOk();
});
