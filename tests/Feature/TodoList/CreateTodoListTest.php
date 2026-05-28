<?php

use App\Models\User;

it('creates a todo list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/lists', ['name' => 'Work'])
        ->assertStatus(201)
        ->assertJson(['data' => ['name' => 'Work']]);

    $this->assertDatabaseHas('todo_lists', ['name' => 'Work', 'user_id' => $user->id]);
});

it('requires authentication to create a todo list', function () {
    $this->postJson('/api/lists', ['name' => 'Work'])
        ->assertStatus(401);
});
