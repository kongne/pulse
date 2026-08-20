<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\TodoList;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    //
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'list_id',
        'title',
        'description',
        'priority',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function list(): BelongsTo
    {
        return $this->BelongsTo(TodoList::class, 'list_id');
    }
}
