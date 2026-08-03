<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 5);

        $categories = Category::latest()->paginate($perPage);
        return Inertia::render('Category/index', ['categories' => $categories, 'filters' => ['per_page' => $perPage]]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:555',

        ]);

        Category::create($validatedData);

        return redirect()->route('category.index')->with('success', 'Category created successfully');
    }

    public function update(Request $request, Category $category)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:555',
        ]);

        $category->update($validated);

        return redirect()->route('category.index')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {

        $category->delete();

        return redirect()->route('category.index')->with('success', 'Category deleted successfully');
    }
}
