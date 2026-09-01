<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\PlanGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $restaurant = app('restaurant');
        $categories = $restaurant->categories()->with(['products' => fn ($q) => $q->orderBy('sort_order')])->orderBy('sort_order')->get();

        return view('panel.products.index', compact('categories', 'restaurant'));
    }

    public function create(): View
    {
        $this->guardHasCategory();

        return view('panel.products.form', [
            'product' => new Product(['is_available' => true]),
            'categories' => app('restaurant')->categories()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(ProductRequest $request, PlanGate $plan): RedirectResponse
    {
        $restaurant = app('restaurant');
        $this->assertCategoryOwned($request->integer('category_id'));

        // Paket sınırı (config/neva.php > plan_features.limits.products)
        $plan->authorizeLimit($restaurant, 'products', $restaurant->products()->count(), 1, 'name');

        $restaurant->products()->create([
            'category_id' => $request->integer('category_id'),
            'name' => $request->name,
            'slug' => $this->uniqueSlug($restaurant->id, $request->name),
            'description' => $request->description,
            'price' => $request->input('price'),
            'discount_price' => $request->input('discount_price'),
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured'),
            'calories' => $request->input('calories'),
            'tags' => $request->preparedTags(),
            'allergens' => $request->preparedAllergens(),
            'sort_order' => (int) $restaurant->products()->where('category_id', $request->integer('category_id'))->max('sort_order') + 1,
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store("restaurants/{$restaurant->id}/products", config('neva.uploads.disk'))
                : null,
        ]);

        return redirect()->route('panel.products.index')->with('success', "“{$request->name}” ürünü eklendi.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('panel.products.form', [
            'product' => $product,
            'categories' => app('restaurant')->categories()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $this->assertCategoryOwned($request->integer('category_id'));

        $data = [
            'category_id' => $request->integer('category_id'),
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->input('price'),
            'discount_price' => $request->input('discount_price'),
            'is_available' => $request->boolean('is_available'),
            'is_featured' => $request->boolean('is_featured'),
            'calories' => $request->input('calories'),
            'tags' => $request->preparedTags(),
            'allergens' => $request->preparedAllergens(),
        ];

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk(config('neva.uploads.disk'))->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store("restaurants/{$product->restaurant_id}/products", config('neva.uploads.disk'));
        }

        $product->update($data);

        return redirect()->route('panel.products.index')->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return back()->with('success', 'Ürün silindi.');
    }

    /**
     * Sürükle-bırak sıralama — sıra KATEGORİ İÇİNDE tutulur.
     * Gönderilen id'ler hem restorana hem de o kategoriye ait olmak zorunda;
     * başka bir kategorinin ürünü bu istekle taşınamaz.
     */
    public function reorder(Request $request): Response|RedirectResponse
    {
        $request->validate([
            'category_id' => ['required', 'integer'],
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $categoryId = $request->integer('category_id');
        $this->assertCategoryOwned($categoryId);

        $restaurant = app('restaurant');

        foreach ($request->input('order') as $position => $id) {
            $restaurant->products()
                ->where('category_id', $categoryId)
                ->whereKey($id)
                ->update(['sort_order' => $position]);
        }

        // Query builder update model olayı fırlatmaz — önbellek sürümünü elle artır.
        $restaurant->bumpMenuVersion();

        return $request->expectsJson() ? response()->noContent() : back();
    }

    private function assertCategoryOwned(int $categoryId): void
    {
        abort_unless(
            app('restaurant')->categories()->whereKey($categoryId)->exists(),
            403
        );
    }

    private function guardHasCategory(): void
    {
        if (! app('restaurant')->categories()->exists()) {
            abort(redirect()->route('panel.categories.create')->with('error', 'Önce en az bir kategori ekleyin.'));
        }
    }

    private function uniqueSlug(int $restaurantId, string $name): string
    {
        $base = Str::slug($name) ?: 'urun';
        $slug = $base;
        $i = 2;

        while (Product::where('restaurant_id', $restaurantId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
