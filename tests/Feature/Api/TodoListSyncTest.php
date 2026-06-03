<?php

use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;

describe('PUT /api/lists/{id}/sync', function () {
    it('replaces all todos in the list from markdown', function () {
        $user = User::factory()->create();
        $list = TodoList::factory()->create(['user_id' => $user->id]);
        $list->todos()->create(['title' => 'Old todo', 'done' => false]);

        $markdown = "- [ ] Buy milk\n- [x] Get eggs\n";

        $this->actingAs($user, 'sanctum')
            ->call('PUT', "/api/lists/{$list->id}/sync", [], [], [], ['CONTENT_TYPE' => 'text/plain'], $markdown)
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name']]);

        expect(Todo::where('todo_list_id', $list->id)->count())->toBe(2);
        $this->assertDatabaseHas('todos', ['title' => 'Buy milk', 'done' => false, 'todo_list_id' => $list->id]);
        $this->assertDatabaseHas('todos', ['title' => 'Get eggs', 'done' => true, 'todo_list_id' => $list->id]);
        $this->assertDatabaseMissing('todos', ['title' => 'Old todo']);
    });

    it('returns 403 when list belongs to another user', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list = TodoList::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')
            ->call('PUT', "/api/lists/{$list->id}/sync", [], [], [], ['CONTENT_TYPE' => 'text/plain'], '- [ ] Task')
            ->assertForbidden();
    });

    it('requires authentication', function () {
        $list = TodoList::factory()->create();

        $this->putJson("/api/lists/{$list->id}/sync", [])->assertUnauthorized();
    });

    it('works with a bearer token', function () {
        $user = User::factory()->create();
        $list = TodoList::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('md-editor');

        $this->call(
            'PUT',
            "/api/lists/{$list->id}/sync",
            [],
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token->plainTextToken,
                'CONTENT_TYPE' => 'text/plain',
            ],
            "- [ ] Mobile task\n"
        )->assertOk();

        $this->assertDatabaseHas('todos', ['title' => 'Mobile task', 'todo_list_id' => $list->id]);
    });
});
