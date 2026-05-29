<?php

use App\Models\TodoList;
use App\Models\User;

describe('TodoList', function () {
    describe('index', function () {
        it('lists todo lists for the authenticated user', function () {
            $user = User::factory()->create();
            TodoList::factory()->count(3)->create(['user_id' => $user->id]);
            TodoList::factory()->create(); // another user's list

            $this->actingAs($user)
                ->getJson('/api/lists')
                ->assertOk()
                ->assertJsonCount(3, 'data');
        });

        it('requires authentication', function () {
            $this->getJson('/api/lists')->assertUnauthorized();
        });
    });

    describe('show', function () {
        it('returns a todo list', function () {
            $list = TodoList::factory()->create(['name' => 'Work']);
            $this->actingAs($list->user)
                ->getJson("/api/lists/{$list->id}")
                ->assertOk()
                ->assertJson(['data' => ['name' => 'Work']]);
        });

        it('returns 404 for a non-existent list', function () {
            $user = User::factory()->create();
            $this->actingAs($user)->getJson('/api/lists/99999')->assertNotFound();
        });

        it('returns 403 for another user\'s list', function () {
            $list = TodoList::factory()->create();
            $other = User::factory()->create();

            $this->actingAs($other)
                ->getJson("/api/lists/{$list->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('returns 404 for a non-existent list', function () {
            $user = User::factory()->create();
            $this->actingAs($user)->patchJson('/api/lists/99999', ['name' => 'X'])->assertNotFound();
        });

        it('renames a todo list', function () {
            $list = TodoList::factory()->create(['name' => 'Old']);
            $this->actingAs($list->user)
                ->patchJson("/api/lists/{$list->id}", ['name' => 'New'])
                ->assertOk()
                ->assertJson(['data' => ['name' => 'New']]);

            expect($list->fresh()->name)->toBe('New');
        });

        it('returns 403 for another user\'s list', function () {
            $list = TodoList::factory()->create();
            $other = User::factory()->create();

            $this->actingAs($other)
                ->patchJson("/api/lists/{$list->id}", ['name' => 'New'])
                ->assertForbidden();
        });

        it('returns 422 when name is empty', function () {
            $list = TodoList::factory()->create();
            $this->actingAs($list->user)
                ->patchJson("/api/lists/{$list->id}", ['name' => ''])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });
    });

    describe('destroy', function () {
        it('returns 404 for a non-existent list', function () {
            $user = User::factory()->create();
            $this->actingAs($user)->deleteJson('/api/lists/99999')->assertNotFound();
        });

        it('deletes a todo list', function () {
            $list = TodoList::factory()->create();
            $this->actingAs($list->user)
                ->deleteJson("/api/lists/{$list->id}")
                ->assertNoContent();

            $this->assertDatabaseMissing('todo_lists', ['id' => $list->id]);
        });

        it('returns 403 for another user\'s list', function () {
            $list = TodoList::factory()->create();
            $other = User::factory()->create();

            $this->actingAs($other)
                ->deleteJson("/api/lists/{$list->id}")
                ->assertForbidden();
        });
    });

    describe('store validation', function () {
        it('returns 422 when name is missing', function () {
            $user = User::factory()->create();
            $this->actingAs($user)
                ->postJson('/api/lists', [])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name']);
        });
    });
});
