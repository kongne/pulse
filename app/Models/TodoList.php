<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;

class TodoList extends Model
{
    //
    use HasFactory;
    protected $table = 'lists';

    protected $fillable = ['name', 'color'];

    public function tasks(): HasMany
    {
        return $this->HasMany(Task::class, 'list_id');
    }
}
