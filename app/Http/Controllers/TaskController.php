<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\TodoList;
use App\Models\Task;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 5);

        $query = Task::query()->with('list:id,name,color');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        $tasks = $query->latest()->paginate($perPage)->withQueryString();

        $lists = TodoList::query()->select(['id', 'name', 'color', 'created_at'])->withCount('tasks')->latest()->get();

        return Inertia::render('tasks/index', ['tasks' => $tasks, 'filters' => ['per_page' => $perPage], 'lists' => $lists, 'filters' => $request->only(['search', 'priority', 'list_id'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string|max:255',
            'list_id' => 'required|exists:lists,id',
            'completed' => 'nullable|boolean',
        ]);

        $validated['completed'] = (bool) ($validated['completed'] ?? false);
        $validated['priority'] = $validated['priority'] ?? 'normal';

        $task = Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:25',
            'description' => 'nullable|string',
            'priority' => 'required|string|max:255',
            'list_id' => 'required|exists:lists,id',
            'completed' => 'nullable|boolean',
        ]);

        $validated['completed'] = (bool) ($validated['completed'] ?? false);
        $validated['priority'] = $validated['priority'] ?? $task->priority;

        $task->update($validated);
        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
