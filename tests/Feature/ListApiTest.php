<?php

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns 401 without credentials', function () {
    $this->getJson('/api/lists')->assertStatus(401);
});

it('returns lists when authenticated via session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/lists')
        ->assertOk()
        ->assertExactJson([]);
});

it('returns lists when authenticated via HTTP Basic Auth', function () {
    $user = User::factory()->create(['password' => bcrypt('secret')]);
    $credentials = base64_encode($user->email.':secret');

    $this->withHeaders(['Authorization' => 'Basic '.$credentials])
        ->getJson('/api/lists')
        ->assertOk()
        ->assertExactJson([]);
});

it('creates a list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/lists', ['name' => 'Shopping'])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Shopping']);

    $this->assertDatabaseHas('lists', ['name' => 'Shopping']);
});

it('renames a list', function () {
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Old Name']);

    $this->actingAs($user)
        ->patchJson("/api/lists/{$list->id}", ['name' => 'New Name'])
        ->assertOk()
        ->assertJsonFragment(['name' => 'New Name']);

    $this->assertDatabaseHas('lists', ['id' => $list->id, 'name' => 'New Name']);
});

it('deletes a list', function () {
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Work']);

    $this->actingAs($user)
        ->deleteJson("/api/lists/{$list->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('lists', ['id' => $list->id]);
});
