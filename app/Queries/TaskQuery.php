<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskQuery
{
    public function apply(
        Builder $query,
        Request $request
    ): Builder {
        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->string('priority')
            );
        }

        if ($request->filled('list_id')) {
            $query->where(
                'list_id',
                $request->integer('list_id')
            );
        }

        return $query;
    }
}
