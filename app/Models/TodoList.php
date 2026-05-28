<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class TodoList extends Model
{
    protected $table = 'lists';

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class, 'list_id');
    }
}
