<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * ProductSeeder
 *
 * Seeds the products table with a realistic, diverse catalogue.
 *
 * Distribution (≈120 products total):
 *   Electronics  25  — high-value hero products, drives AI revenue insight
 *   Sports       22  — broad gym + outdoor range
 *   Fashion      20  — local + global brands
 *   Food         20  — Indonesian + imported premium food
 *   Books        20  — tech + non-tech mix
 *   Home         20  — smart home + lifestyle
 *   Health       20  — wellness + skincare
 *   Automotive   18  — accessories + parts
 *
 * Usage:
 *   php artisan db:seed --class=ProductSeeder          # append
 *   php artisan migrate:fresh --seed                   # full reset + seed
 *
 * The factory uses a slug uniqueness workaround (appends a random number),
 * so it is safe to call multiple times without unique-slug conflicts.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱  Seeding products…');

        // Clear existing products so analytics/AI sees fresh data
        Product::truncate();

        // ── Per-category batches ─────────────────────────────────────────────
        // Each call uses the named factory state so only products from that
        // category's catalogue are generated.

        $batches = [
            'electronics' => 25,
            'sports'      => 22,
            'fashion'     => 20,
            'food'        => 20,
            'books'       => 20,
            'home'        => 20,
            'health'      => 20,
            'automotive'  => 18,
        ];

        foreach ($batches as $state => $count) {
            Product::factory()
                ->count($count)
                ->$state()
                ->create();

            $this->command->line("  ✓  {$count} {$state} products created");
        }

        // ── Force a handful of highly-recommended products ───────────────────
        // Ensures the AI recommendation endpoint always has good candidates
        // across diverse categories.
        $recommendedCounts = [
            'electronics' => 4,
            'sports'      => 3,
            'fashion'     => 2,
            'food'        => 2,
            'books'       => 2,
            'home'        => 2,
            'health'      => 2,
            'automotive'  => 1,
        ];

        $this->command->line('');
        $this->command->line('  Injecting recommended products…');

        foreach ($recommendedCounts as $state => $count) {
            Product::factory()
                ->count($count)
                ->$state()
                ->recommended()
                ->create();
        }

        $total = Product::count();
        $recommended = Product::where('is_recommended', true)->count();

        $this->command->info('');
        $this->command->info("✅  Done! {$total} products seeded ({$recommended} recommended).");
    }
}
