<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    //
    public function index()
    {
        $properties = Property::latest()->paginate(5);
        return Inertia::render('Property/index', ['properties' => $properties]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'price' => 'nullable|numeric',
        ]);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('property_images', 'public');
            $validatedData['image'] = $imagePath;
        }

        Property::create($validatedData);

        return redirect()->route('property.index')->with('success', 'Property created successfully.');
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'price' => 'nullable|numeric',
        ]);
        if ($request->hasFile('image')) {
            if ($property->image && Storage::disk('public')->exists($property->image)) {
                Storage::disk('public')->delete($property->image);
            }
            $imagePath = $request->file('image')->store('property_images', 'public');
            $validated['image'] = $imagePath;
        }
        $property->update($validated);
        return redirect()->route('property.index')->with('success', 'Property updated successfully.');
    }
    public function destroy(Property $property)
    {
        if ($property->image && Storage::disk('public')->exists($property->image)) {
            Storage::disk('public')->delete($property->image);
        }

        $property->delete();

        return redirect()->route('property.index')->with('success', 'Property deleted successfully.');
    }
}
