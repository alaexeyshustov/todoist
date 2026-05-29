<?php

use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;

describe('GET /api/lists/{id}/export', function () {
    it('returns a markdown file for the list owner', function () {
        $list = TodoList::factory()->create(['name' => 'Shopping']);
        Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Buy milk', 'done' => false]);

        $this->actingAs($list->user)
            ->get("/api/lists/{$list->id}/export")
            ->assertOk()
            ->assertDownload('shopping.md')
            ->assertSee('# Shopping')
            ->assertSee('- [ ] Buy milk');
    });

    it('returns 403 for another user\'s list', function () {
        $list = TodoList::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get("/api/lists/{$list->id}/export")
            ->assertForbidden();
    });

    it('requires authentication', function () {
        $list = TodoList::factory()->create();

        $this->get("/api/lists/{$list->id}/export")
            ->assertUnauthorized();
    });

    it('uses list-{id}.md as filename when the list name produces an empty slug', function () {
        $list = TodoList::factory()->create(['name' => '日本語']);

        $this->actingAs($list->user)
            ->get("/api/lists/{$list->id}/export")
            ->assertOk()
            ->assertDownload("list-{$list->id}.md");
    });
});
