<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

describe('POST /api/lists/import', function () {
    it('creates a new list from an uploaded markdown file', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('shopping.md', "# Shopping\n\n- [ ] Buy milk\n- [x] Get eggs\n");

        $this->actingAs($user)
            ->post('/api/lists/import', ['file' => $file])
            ->assertCreated()
            ->assertJson(['data' => ['name' => 'Shopping']]);

        $this->assertDatabaseHas('todo_lists', ['name' => 'Shopping', 'user_id' => $user->id]);
        $this->assertDatabaseHas('todos', ['title' => 'Buy milk', 'done' => false]);
        $this->assertDatabaseHas('todos', ['title' => 'Get eggs', 'done' => true]);
    });

    it('returns 422 when no file is provided', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/lists/import', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });

    it('requires authentication', function () {
        $file = UploadedFile::fake()->createWithContent('shopping.md', '# Shopping');

        $this->post('/api/lists/import', ['file' => $file])
            ->assertUnauthorized();
    });

    it('returns 422 for a non-markdown file extension', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('data.csv', "title,done\nBuy milk,false");

        $this->actingAs($user)
            ->post('/api/lists/import', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });

    it('uses the name override when provided', function () {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('shopping.md', "# Shopping\n\n- [ ] Buy milk\n");

        $this->actingAs($user)
            ->post('/api/lists/import', ['file' => $file, 'name' => 'My Groceries'])
            ->assertCreated()
            ->assertJson(['data' => ['name' => 'My Groceries']]);
    });
});
