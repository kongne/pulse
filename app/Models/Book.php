<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'price', 'cover_image', 'categories_id'];

    public function category(): BelongsTo
    {
        return $this->BelongsTo(Category::class);
    }
}
