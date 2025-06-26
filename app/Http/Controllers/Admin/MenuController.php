<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $query = Menu::query();

    if ($search = $request->input('search')) {
        $query->where('name', 'like', '%' . $search . '%')
              ->orWhere('category', 'like', '%' . $search . '%');
    }

    $menus = $query->orderBy('category')->orderBy('name')->paginate(10);

    return view('admin.menu.index', compact('menus'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Menu::getCategoryOptions();
        return view('admin.menu.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => ['required', Rule::in(array_keys(Menu::getCategoryOptions()))],
            'is_available' => 'boolean',
            'preparation_time' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menu_images', 'public');
            $validated['image'] = $imagePath;
        }

        if (isset($validated['discount_percentage']) && $validated['discount_percentage'] > 0) {
            $validated['discounted_price'] = $validated['price'] * (1 - ($validated['discount_percentage'] / 100));
        } else {
            $validated['discounted_price'] = null;
            $validated['discount_percentage'] = null;
        }

        $validated['is_available'] = $request->has('is_available');

        Menu::create($validated);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu item added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $categories = Menu::getCategoryOptions();
        return view('admin.menu.edit', compact('menu', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => ['required', Rule::in(array_keys(Menu::getCategoryOptions()))],
            'is_available' => 'boolean',
            'preparation_time' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $imagePath = $request->file('image')->store('menu_images', 'public');
            $validated['image'] = $imagePath;
        }

        if (isset($validated['discount_percentage']) && $validated['discount_percentage'] > 0) {
            $validated['discounted_price'] = $validated['price'] * (1 - ($validated['discount_percentage'] / 100));
        } else {
            $validated['discounted_price'] = null;
            $validated['discount_percentage'] = null;
        }

        $validated['is_available'] = $request->has('is_available');

        $menu->update($validated);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu item deleted successfully.');
    }
}