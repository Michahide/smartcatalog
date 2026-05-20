<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhereJsonContains('tags', $request->search);
            });
        }

        $products = $query->orderByDesc('is_recommended')
                          ->orderByDesc('rating')
                          ->paginate(20);

        return response()->json($products);
    }

    public function recommended()
    {
        $products = Cache::remember('products.recommended', 300, function () {
            return Product::where('is_recommended', true)
                          ->orderByDesc('rating')
                          ->take(6)
                          ->get();
        });

        return response()->json($products);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string',
            'price'          => 'required|integer|min:0',
            'rating'         => 'nullable|numeric|between:0,5',
            'emoji'          => 'nullable|string',
            'description'    => 'nullable|string',
            'stock'          => 'required|integer|min:0',
            'tags'           => 'nullable|array',
            'is_recommended' => 'boolean',
        ]);

        $product = Product::create($validated);
        Cache::forget('products.recommended');

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'price'          => 'sometimes|integer|min:0',
            'stock'          => 'sometimes|integer|min:0',
            'is_recommended' => 'sometimes|boolean',
        ]);

        $product->update($validated);
        Cache::forget('products.recommended');

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        Cache::forget('products.recommended');

        return response()->json(null, 204);
    }
}
