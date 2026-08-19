<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\TodoList;
use App\Models\Task;


class AboutController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Overview
        |--------------------------------------------------------------------------
        */

        $lists = TodoList::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('completed', true);
                },
            ])
            ->latest()
            ->get();

        $recentTasks = Task::query()
            ->with('list:id,name,color')
            ->latest()
            ->take(10)
            ->get();

        $totalTasks = Task::count();

        $completedTasks = Task::query()
            ->where('completed', true)
            ->count();

        $pendingTasks = Task::query()
            ->where('completed', false)
            ->count();

        return Inertia::render('about', [
            'lists' => $lists,
            'recentTasks' => $recentTasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
        ]);
    }
}
