<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;

class PropertyController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Property');
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'price' => 'nullable|numeric',
        ]);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('property_images', 'public');
            $validatedData['image'] = $imagePath;
        }

        Property::create($validatedData);

        return redirect()->back()->with('success', 'Property created successfully.');
    }
}
