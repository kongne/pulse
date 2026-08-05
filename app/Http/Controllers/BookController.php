<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use Inertia\Inertia;


class BookController extends Controller
{
    //
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 5);

        $books = Book::with('category')->paginate($perPage);

        return Inertia::render('Book/index', ['books' => $books, 'categories' => Category::OrderBy('name')->get(), 'filters' => ['per_page' => $perPage]]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories_id' => 'required|exists:categories,id',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $validatedData['cover_image'] = $request
                ->file('cover_image')
                ->store('covers', 'public');
        }

        Book::create($validatedData);

        return redirect()->route('book.index')->with('success', 'Book created successfully.');
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories_id' => 'required|exists:categories,id',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request
                ->file('cover_image')
                ->store('covers', 'public');
        }
        $book->update($validated);
        return redirect()->route('book.index')->with('success', 'Book updated successfully.');
    }
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('book.index')->with('success', 'Book deleted successfully.');
    }

    //force delete

    /*public function forceDelete(int $id)
    {
        $property = Property::withTrashed()->findOrFail($id);

        if ($property->image && Storage::disk('public')->exists($property->image)) {
            Storage::disk('public')->delete($property->image);
        }

        $property->forceDelete();

        return redirect()
            ->route('property.index')
            ->with('success', 'Property permanently deleted.');
    }*/
    //restore deleted items
    /*public function restore(int $id)
    {
        $property = Property::withTrashed()->findOrFail($id);

        $property->restore();

        return redirect()
            ->route('property.index')
            ->with('success', 'Property restored successfully.');
    }*/
    // method to query only trashed items Property::onlyTrashed()->get();
    //method to query all items regardless they have been deleted or not Property::withTrashed()->get();
}
