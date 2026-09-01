<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = app('restaurant')->categories()->withCount('products')->orderBy('sort_order')->get();

        return view('panel.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('panel.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $restaurant = app('restaurant');

        $category = $restaurant->categories()->create([
            'name' => $request->name,
            'slug' => $this->uniqueSlug($restaurant->id, $request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) $restaurant->categories()->max('sort_order') + 1,
        ]);

        return redirect()->route('panel.categories.index')->with('success', "“{$category->name}” kategorisi eklendi.");
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('panel.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('panel.categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return back()->with('success', 'Kategori silindi.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        foreach ($request->input('order') as $position => $id) {
            app('restaurant')->categories()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }

    private function uniqueSlug(int $restaurantId, string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 2;

        while (Category::where('restaurant_id', $restaurantId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
