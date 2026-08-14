<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TodoList;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ListController extends Controller
{

    public function index(): Response
    {
        $lists = TodoList::query()->select(['id', 'name', 'color', 'created_at'])->withCount('tasks')->latest()->get();
        return Inertia::render('lists/index', ['lists' => $lists]);
    }
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $list = TodoList::create($validated);

        return redirect()->route('lists.index')->with('success', 'List created successfully.');
    }
    public function update(Request $request, TodoList $list): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $list->update($validated);

        return redirect()->route('lists.index')->with('success', 'List updated successfully.');
    }
    public function destroy(TodoList $list): RedirectResponse
    {
        $list->delete();

        return redirect()->route('lists.index')->with('success', 'List deleted successfully.');
    }
}
