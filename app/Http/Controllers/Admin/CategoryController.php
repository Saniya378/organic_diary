<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with product counts.
     */
    public function index(): View
    {
        $categories = Category::withCount('products')->latest()->get();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Ensure slug uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', "Category '{$validated['name']}' created successfully.");
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Check if slug changed and conflicts
        if ($slug !== $category->slug) {
            $baseSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because it contains {$category->products()->count()} products. Please reassign or delete the products first.");
        }

        $category->delete();

        return back()->with('success', "Category '{$category->name}' deleted successfully.");
    }
}
