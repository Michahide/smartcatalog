<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->validate(['q' => 'required|string|min:2|max:200'])['q'];

        // Keyword tokenisation
        $keywords = collect(explode(' ', strtolower($query)))
            ->filter(fn($k) => strlen($k) > 1)
            ->unique()
            ->values();

        // Map Indonesian synonyms → categories
        $categoryMap = [
            'elektronik' => 'Electronics', 'tech' => 'Electronics', 'gadget' => 'Electronics',
            'fashion' => 'Fashion', 'baju' => 'Fashion', 'outfit' => 'Fashion', 'pakaian' => 'Fashion',
            'olahraga' => 'Sports', 'gym' => 'Sports', 'fitness' => 'Sports', 'sport' => 'Sports',
            'buku' => 'Books', 'book' => 'Books', 'baca' => 'Books', 'novel' => 'Books',
            'makanan' => 'Food', 'minuman' => 'Food', 'kopi' => 'Food',
        ];

        $matchedCategories = $keywords
            ->map(fn($k) => $categoryMap[$k] ?? null)
            ->filter()
            ->unique()
            ->values();

        // Score products
        $products = Product::all()->map(function (Product $product) use ($keywords, $matchedCategories) {
            $score = 0.0;
            $name = strtolower($product->name);
            $cat  = strtolower($product->category);

            foreach ($keywords as $kw) {
                if (str_contains($name, $kw)) $score += 0.5;
                if (str_contains($cat, $kw))  $score += 0.3;
            }

            foreach ($matchedCategories as $cat) {
                if ($product->category === $cat) $score += 0.6;
            }

            $score += $product->rating * 0.04;
            if ($product->is_recommended) $score += 0.1;

            $product->relevance_score = round(min($score, 1.0), 2);
            return $product;
        })
        ->filter(fn($p) => $p->relevance_score > 0.1)
        ->sortByDesc('relevance_score')
        ->take(8)
        ->values();

        return response()->json([
            'query'   => $query,
            'results' => $products,
            'total'   => $products->count(),
        ]);
    }
}
