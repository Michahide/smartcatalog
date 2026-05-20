<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo user
        User::firstOrCreate(
            ['email' => 'michael@dipa.co.id'],
            [
                'name'        => 'Michael',
                'password'    => Hash::make('password'),
                'preferences' => ['Electronics', 'Tech'],
            ]
        );

        // Products
        $products = [
            ['name' => 'iPhone 15 Pro',       'category' => 'Electronics', 'price' => 18500000, 'rating' => 4.8, 'rating_count' => 1243, 'emoji' => '📱', 'stock' => 15, 'tags' => ['Electronics','Tech'],         'is_recommended' => true],
            ['name' => 'MacBook Air M3',       'category' => 'Electronics', 'price' => 21900000, 'rating' => 4.9, 'rating_count' => 879,  'emoji' => '💻', 'stock' => 8,  'tags' => ['Electronics','Tech'],         'is_recommended' => true],
            ['name' => 'Sony WH-1000XM5',      'category' => 'Electronics', 'price' => 5200000,  'rating' => 4.7, 'rating_count' => 654,  'emoji' => '🎧', 'stock' => 22, 'tags' => ['Electronics'],                'is_recommended' => false],
            ['name' => 'Logitech MX Master 3', 'category' => 'Electronics', 'price' => 1400000,  'rating' => 4.6, 'rating_count' => 431,  'emoji' => '🖱️', 'stock' => 35, 'tags' => ['Electronics'],                'is_recommended' => false],
            ['name' => 'Nike Air Max 2024',    'category' => 'Sports',      'price' => 2300000,  'rating' => 4.5, 'rating_count' => 321,  'emoji' => '👟', 'stock' => 44, 'tags' => ['Sports','Fashion'],           'is_recommended' => true],
            ['name' => 'Whey Protein Gold',    'category' => 'Sports',      'price' => 450000,   'rating' => 4.4, 'rating_count' => 567,  'emoji' => '💪', 'stock' => 88, 'tags' => ['Sports'],                     'is_recommended' => false],
            ['name' => 'Atomic Habits',        'category' => 'Books',       'price' => 89000,    'rating' => 4.9, 'rating_count' => 2341, 'emoji' => '📚', 'stock' => 120,'tags' => ['Books'],                      'is_recommended' => true],
            ['name' => 'Uniqlo HEATTECH Tee',  'category' => 'Fashion',     'price' => 199000,   'rating' => 4.3, 'rating_count' => 890,  'emoji' => '👕', 'stock' => 200,'tags' => ['Fashion'],                    'is_recommended' => false],
            ['name' => 'Kopi Flores AAA',      'category' => 'Food',        'price' => 125000,   'rating' => 4.7, 'rating_count' => 445,  'emoji' => '☕', 'stock' => 66, 'tags' => ['Food'],                       'is_recommended' => false],
            ['name' => 'iPad Pro M4',          'category' => 'Electronics', 'price' => 16800000, 'rating' => 4.8, 'rating_count' => 667,  'emoji' => '📟', 'stock' => 12, 'tags' => ['Electronics','Tech'],         'is_recommended' => true],
            ['name' => "Levi's 511 Slim",      'category' => 'Fashion',     'price' => 699000,   'rating' => 4.4, 'rating_count' => 334,  'emoji' => '👖', 'stock' => 77, 'tags' => ['Fashion'],                    'is_recommended' => false],
            ['name' => 'Yoga Mat Premium',     'category' => 'Sports',      'price' => 380000,   'rating' => 4.6, 'rating_count' => 278,  'emoji' => '🧘', 'stock' => 55, 'tags' => ['Sports'],                     'is_recommended' => false],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
