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
            $totalProducts = Product::count();

            $salesByCategory = Product::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get()
                ->mapWithKeys(fn($r) => [$r->category => $r->count]);

            $total = $salesByCategory->sum();

            $colorMap = [
                'Electronics' => '#8B5CF6',
                'Fashion'     => '#06B6D4',
                'Sports'      => '#10B981',
                'Food'        => '#F59E0B',
                'Books'       => '#EC4899',
            ];

            return [
                'total_products'     => $totalProducts,
                'ai_recommendations' => '1.2K',
                'active_sessions'    => rand(60, 120),
                'revenue_today'      => 4200000,
                'revenue_formatted'  => 'Rp 4.2M',

                'sales_by_category'  => $salesByCategory->map(function ($count, $cat) use ($total, $colorMap) {
                    return [
                        'category'   => $cat,
                        'percentage' => $total > 0 ? round($count / $total * 100) : 0,
                        'color'      => $colorMap[$cat] ?? '#94A3B8',
                    ];
                })->values(),

                'traffic_sources' => [
                    ['source' => 'Organic', 'percentage' => 39],
                    ['source' => 'Direct',  'percentage' => 24],
                    ['source' => 'Social',  'percentage' => 15],
                    ['source' => 'Other',   'percentage' => 22],
                ],

                'ai_performance' => [
                    'ctr'         => 34,
                    'revenue_lift'=> 7.2,
                    'accuracy'    => 92,
                    'avg_latency' => 1.8,
                ],

                'activity_log' => [
                    ['icon' => '🤖', 'text' => 'AI merekomendasikan 3 produk ke User #4821', 'time' => '2m ago',  'color' => 'bg-violet-500/15'],
                    ['icon' => '🛒', 'text' => 'Pembelian: iPhone 15 Pro – Rp 18.5jt',      'time' => '5m ago',  'color' => 'bg-emerald-500/15'],
                    ['icon' => '🔍', 'text' => 'Semantic search: "earphone noise cancel"',  'time' => '8m ago',  'color' => 'bg-cyan-500/15'],
                    ['icon' => '💬', 'text' => 'Chatbot session dimulai oleh User #3309',    'time' => '12m ago', 'color' => 'bg-amber-500/15'],
                    ['icon' => '⚠️', 'text' => 'Stok Bose QC45 tersisa 2 unit',              'time' => '18m ago', 'color' => 'bg-red-500/15'],
                ],
            ];
        });

        return response()->json($data);
    }
}
