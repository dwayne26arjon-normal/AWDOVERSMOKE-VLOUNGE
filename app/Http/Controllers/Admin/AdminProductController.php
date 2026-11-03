<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'productCount' => Product::count(),
            'inventoryUnits' => Product::sum('stock'),
            'wellStockUnits' => Product::where('stock', '>', 0)->sum('stock'),
            'lowStockCount' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'outOfStockCount' => Product::where('stock', 0)->count(),
            'inventoryValue' => Product::selectRaw('COALESCE(SUM(price * stock), 0) as total')->value('total'),
            'recentProducts' => Product::latest()->limit(5)->get(),
            'lowStockProducts' => Product::where('stock', '<=', 10)->orderBy('stock')->limit(5)->get(),
        ]);
    }

    public function products(Request $request): View
    {
        $products = Product::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Product::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function inventory(Request $request): View
    {
        $products = Product::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->input('stock') === 'low', fn ($query) => $query->where('stock', '>', 0)->where('stock', '<=', 10))
            ->when($request->input('stock') === 'empty', fn ($query) => $query->where('stock', 0))
            ->when($request->input('sort') === 'low', fn ($query) => $query->orderByRaw('CASE WHEN stock = 0 THEN 1 ELSE 0 END')->orderBy('stock'))
            ->when($request->input('sort') === 'name', fn ($query) => $query->orderBy('name'))
            ->when($request->input('sort') === 'stock', fn ($query) => $query->orderByDesc('stock'))
            ->when(! in_array($request->input('sort'), ['low', 'name', 'stock'], true), fn ($query) => $query->latest())
            ->paginate(10)
            ->withQueryString();

        return view('admin.inventory.index', [
            'products' => $products,
            'productCount' => Product::count(),
            'inStockProductCount' => Product::where('stock', '>', 0)->count(),
            'categories' => Product::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category'),
            'inventoryUnits' => Product::sum('stock'),
            'wellStockUnits' => Product::where('stock', '>', 0)->sum('stock'),
            'lowStockCount' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'outOfStockCount' => Product::where('stock', 0)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($image) {
            $data['image_path'] = $image->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($image) {
            $oldImage = $product->image_path;
            $data['image_path'] = $image->store('products', 'public');
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted successfully.');
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
