<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index()
    {
        $data = Cache::remember('analytics.dashboard', 30, function () {

            // ── Core counts ──────────────────────────────────────────────────
            $totalProducts   = Product::count();
            $recommendedCount = Product::where('is_recommended', true)->count();

            // ── Sales by category (product count as proxy for catalogue weight)
            $categoryStats = Product::selectRaw('category, COUNT(*) as count, AVG(rating) as avg_rating, SUM(price) as total_price_pool')
                ->groupBy('category')
                ->orderByDesc('count')
                ->get();

            $totalCount = $categoryStats->sum('count');

            $colorMap = [
                'Electronics' => '#8B5CF6',
                'Fashion'     => '#06B6D4',
                'Sports'      => '#10B981',
                'Food'        => '#F59E0B',
                'Books'       => '#EC4899',
                'Home'        => '#F97316',
                'Health'      => '#14B8A6',
                'Automotive'  => '#6366F1',
            ];

            $salesByCategory = $categoryStats->map(function ($row) use ($totalCount, $colorMap) {
                return [
                    'category'   => $row->category,
                    'count'      => $row->count,
                    'avg_rating' => round($row->avg_rating, 2),
                    'percentage' => $totalCount > 0 ? round($row->count / $totalCount * 100) : 0,
                    'color'      => $colorMap[$row->category] ?? '#94A3B8',
                ];
            })->values();

            // ── Revenue estimate (avg price × stock as proxy for GMV potential)
            $gmvPotential = Product::selectRaw('SUM(price * stock) as gmv')->value('gmv') ?? 0;
            $revenueToday = (int) ($gmvPotential * 0.002); // simulate 0.2% sell-through/day
            $revenueFormatted = $revenueToday >= 1_000_000
                ? 'Rp ' . number_format($revenueToday / 1_000_000, 1) . 'M'
                : 'Rp ' . number_format($revenueToday / 1000, 0) . 'K';

            // ── Top performing products (for AI activity log) ─────────────────
            $topProducts = Product::where('is_recommended', true)
                ->orderByDesc('rating')
                ->take(5)
                ->get(['name', 'price', 'category', 'emoji', 'rating']);

            // ── AI Recommendation quality metrics ─────────────────────────────
            $avgRating          = Product::avg('rating');
            $highRatedRatio     = Product::where('rating', '>=', 4.5)->count() / max($totalProducts, 1);
            $recommendedRatio   = $recommendedCount / max($totalProducts, 1);

            $aiAccuracy         = (int) min(98, round(($avgRating / 5.0) * 85 + $highRatedRatio * 15));
            $aiCtr              = (int) min(60, round($recommendedRatio * 180 + 18));
            $revenueLift        = round(($highRatedRatio * 12) + 3.5, 1);
            $avgLatency         = round(1.2 + (mt_rand(0, 100) / 100) * 0.8, 1);

            // ── Low-stock alerts (real data) ──────────────────────────────────
            $lowStockProducts = Product::where('stock', '<=', 5)
                ->orderBy('stock')
                ->take(3)
                ->get(['name', 'stock', 'emoji']);

            // ── Dynamic activity log ──────────────────────────────────────────
            $activityLog = [];

            // AI recommendation event
            if ($topProducts->isNotEmpty()) {
                $p = $topProducts->random();
                $activityLog[] = [
                    'icon'  => '🤖',
                    'text'  => "AI merekomendasikan {$p->name} ke User #" . rand(1000, 9999),
                    'time'  => rand(1, 3) . 'm ago',
                    'color' => 'bg-violet-500/15',
                ];
            }

            // Purchase event
            if ($topProducts->isNotEmpty()) {
                $p = $topProducts->random();
                $price = $p->price >= 1_000_000
                    ? 'Rp ' . number_format($p->price / 1_000_000, 1) . 'jt'
                    : 'Rp ' . number_format($p->price / 1000, 0) . 'rb';
                $activityLog[] = [
                    'icon'  => '🛒',
                    'text'  => "Pembelian: {$p->emoji} {$p->name} – {$price}",
                    'time'  => rand(4, 8) . 'm ago',
                    'color' => 'bg-emerald-500/15',
                ];
            }

            // Search event
            $searchTerms = ['earphone noise cancel', 'protein gym', 'buku programming', 'kopi arabica', 'smartwatch murah', 'vitamin c 1000mg'];
            $activityLog[] = [
                'icon'  => '🔍',
                'text'  => 'Semantic search: "' . $searchTerms[array_rand($searchTerms)] . '"',
                'time'  => rand(6, 12) . 'm ago',
                'color' => 'bg-cyan-500/15',
            ];

            // Chat session
            $activityLog[] = [
                'icon'  => '💬',
                'text'  => 'Chatbot session dimulai oleh User #' . rand(1000, 9999),
                'time'  => rand(10, 18) . 'm ago',
                'color' => 'bg-amber-500/15',
            ];

            // Low-stock alert (real data)
            foreach ($lowStockProducts->take(1) as $ls) {
                $activityLog[] = [
                    'icon'  => '⚠️',
                    'text'  => "Stok {$ls->emoji} {$ls->name} tersisa {$ls->stock} unit",
                    'time'  => rand(15, 30) . 'm ago',
                    'color' => 'bg-red-500/15',
                ];
            }

            // ── Insights summary ──────────────────────────────────────────────
            $topCategory = $categoryStats->sortByDesc('count')->first();
            $insights = [
                'top_category'      => $topCategory?->category,
                'top_category_pct'  => $topCategory ? round($topCategory->count / $totalCount * 100) : 0,
                'recommended_ratio' => round($recommendedRatio * 100, 1),
                'avg_product_rating'=> round($avgRating, 2),
                'low_stock_count'   => $lowStockProducts->count(),
                'gmv_potential_formatted' => $gmvPotential >= 1_000_000_000
                    ? 'Rp ' . number_format($gmvPotential / 1_000_000_000, 1) . 'M'
                    : 'Rp ' . number_format($gmvPotential / 1_000_000, 1) . 'jt',
            ];

            return [
                // ── KPI cards
                'total_products'     => $totalProducts,
                'recommended_count'  => $recommendedCount,
                'ai_recommendations' => number_format($recommendedCount * rand(180, 350)) . '+',
                'active_sessions'    => rand(60, 120),
                'revenue_today'      => $revenueToday,
                'revenue_formatted'  => $revenueFormatted,

                // ── Charts
                'sales_by_category' => $salesByCategory,

                'traffic_sources' => [
                    ['source' => 'Organic', 'percentage' => 39],
                    ['source' => 'Direct',  'percentage' => 24],
                    ['source' => 'Social',  'percentage' => 15],
                    ['source' => 'Other',   'percentage' => 22],
                ],

                // ── AI performance (derived from real product quality)
                'ai_performance' => [
                    'ctr'          => $aiCtr,
                    'revenue_lift' => $revenueLift,
                    'accuracy'     => $aiAccuracy,
                    'avg_latency'  => $avgLatency,
                ],

                // ── Live feed
                'activity_log' => $activityLog,

                // ── Business insights block
                'insights' => $insights,
            ];
        });

        return response()->json($data);
    }
}
