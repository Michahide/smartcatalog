<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 *
 * Generates realistic Indonesian e-commerce product data across 8 categories.
 * Run via: php artisan db:seed --class=ProductSeeder
 *          or php artisan db:seed  (if registered in DatabaseSeeder)
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    // ──────────────────────────────────────────────────────────────────────────
    // Category catalogue — each entry drives a fully-typed product
    // ──────────────────────────────────────────────────────────────────────────

    private array $catalogue = [

        // ── Electronics ──────────────────────────────────────────────────────
        'Electronics' => [
            'emoji'    => ['📱','💻','🎧','🖥️','⌨️','🖱️','📷','🎮','📺','🔌','🖨️','💾'],
            'products' => [
                ['Samsung Galaxy S24 Ultra',   22_000_000, 26_000_000],
                ['iPhone 15 Pro Max',           24_000_000, 28_000_000],
                ['Google Pixel 8 Pro',          14_000_000, 17_000_000],
                ['MacBook Pro M3 14"',          26_000_000, 32_000_000],
                ['ASUS ROG Zephyrus G14',       22_000_000, 28_000_000],
                ['Dell XPS 15 OLED',            21_000_000, 25_000_000],
                ['Sony WH-1000XM5',              5_000_000,  6_500_000],
                ['Bose QuietComfort 45',         4_500_000,  5_800_000],
                ['AirPods Pro 2nd Gen',          4_000_000,  4_800_000],
                ['Sony A7 IV Mirrorless',       40_000_000, 48_000_000],
                ['Canon EOS R6 Mark II',        35_000_000, 42_000_000],
                ['GoPro Hero 12 Black',          7_500_000,  9_000_000],
                ['iPad Pro M4 13"',             19_000_000, 23_000_000],
                ['Samsung Galaxy Tab S9 Ultra', 16_000_000, 20_000_000],
                ['Apple Watch Series 9',         6_500_000,  8_000_000],
                ['Garmin Fenix 7X',             10_000_000, 13_000_000],
                ['DJI Mini 4 Pro Drone',        12_000_000, 15_000_000],
                ['PlayStation 5 Slim',          10_000_000, 12_000_000],
                ['Xbox Series X',                9_000_000, 11_000_000],
                ['Nintendo Switch OLED',         5_000_000,  6_200_000],
                ['LG OLED C3 65"',              25_000_000, 32_000_000],
                ['Samsung Neo QLED 8K 65"',     38_000_000, 48_000_000],
                ['Logitech MX Keys S',           1_800_000,  2_300_000],
                ['Razer DeathAdder V3',          1_200_000,  1_600_000],
                ['Anker PowerBank 24000mAh',       800_000,  1_100_000],
                ['Xiaomi Mi Air Purifier 4',     3_000_000,  4_000_000],
            ],
            'tags'    => ['Electronics', 'Tech', 'Gadget'],
        ],

        // ── Sports ───────────────────────────────────────────────────────────
        'Sports' => [
            'emoji'    => ['🏋️','⚽','🏊','🎽','👟','🚴','🏸','🥊','🧘','⛹️','🎯','🏄'],
            'products' => [
                ['Nike Air Max 270',              2_200_000,  2_800_000],
                ['Adidas Ultraboost 23',          2_500_000,  3_200_000],
                ['New Balance 990v6',             3_200_000,  4_000_000],
                ['Gym Power Rack Olympic',       12_000_000, 18_000_000],
                ['Bowflex SelectTech 552',        8_000_000, 10_000_000],
                ['Adjustable Dumbbell Set 40kg',  4_500_000,  6_000_000],
                ['Whey Protein Isolate 5lb',        800_000,  1_200_000],
                ['Creatine Monohydrate 500g',       150_000,    250_000],
                ['Pre-Workout Energy 300g',          350_000,    500_000],
                ['BCAA Recovery Drink 400g',         200_000,    320_000],
                ['Carbon Fiber Bicycle 700c',     8_000_000, 14_000_000],
                ['Peloton Smart Bike+',           35_000_000, 42_000_000],
                ['Garmin Edge 540 Cycling GPS',    7_000_000,  9_000_000],
                ['Yoga Mat Lululemon 5mm',           900_000,  1_300_000],
                ['Resistance Band Set 11pcs',        200_000,    350_000],
                ['Battle Rope 15m 38mm',             800_000,  1_200_000],
                ['Foam Roller High-Density',         250_000,    400_000],
                ['Wilson Clash 100 Tennis',        3_500_000,  4_800_000],
                ['Yonex Astrox 99 Pro Badminton',  4_500_000,  5_500_000],
                ['Speedo Fastskin LZR Swimsuit',   3_000_000,  4_200_000],
                ['Kettlebell 20kg Cast Iron',        650_000,    900_000],
                ['Pull-up Bar Doorway',              350_000,    600_000],
                ['Jump Rope Speed Cable',            150_000,    250_000],
            ],
            'tags' => ['Sports', 'Fitness', 'Outdoor'],
        ],

        // ── Fashion ───────────────────────────────────────────────────────────
        'Fashion' => [
            'emoji'    => ['👗','👕','👖','🧥','👠','👜','🧢','⌚','💍','🕶️','🧣','🧤'],
            'products' => [
                ["Levi's 501 Original Jeans",      800_000,  1_200_000],
                ['Zara Trench Coat Premium',      1_500_000,  2_200_000],
                ['Uniqlo Ultra Light Down Jacket', 700_000,  1_000_000],
                ['H&M Premium Linen Shirt',        250_000,    450_000],
                ['Tote Bag Canvas Branded',        300_000,    600_000],
                ['Longchamp Le Pliage Bag',       2_000_000,  2_800_000],
                ['Nike Dri-FIT Training Shirt',    400_000,    650_000],
                ['Adidas Originals Trefoil Hoodie',700_000,  1_100_000],
                ['Ray-Ban Aviator Classic',       2_000_000,  2_800_000],
                ['Fossil Grant Chronograph Watch',2_500_000,  3_500_000],
                ['ALDO Oxford Leather Shoes',     1_200_000,  1_800_000],
                ['Vans Old Skool Checkerboard',    900_000,  1_300_000],
                ['Keds Triple Decker Sneaker',     700_000,  1_000_000],
                ['Celana Chino Slim Fit Cotton',   300_000,    550_000],
                ['Batik Tulis Solo Premium',       600_000,  1_200_000],
                ['Kebaya Modern Beaded',         1_200_000,  2_500_000],
                ['Puma Essential Jogger Pants',    550_000,    800_000],
                ['Columbia Omni-Tech Rain Jacket', 2_000_000,  2_800_000],
                ['Topi Bucket Embroidered',        200_000,    400_000],
                ['Scarf Wool Merino 180cm',        350_000,    600_000],
            ],
            'tags' => ['Fashion', 'Style', 'Apparel'],
        ],

        // ── Food & Beverage ───────────────────────────────────────────────────
        'Food' => [
            'emoji'    => ['☕','🍵','🍫','🫙','🌶️','🫒','🍯','🧃','🥜','🍚','🌿','🫖'],
            'products' => [
                ['Kopi Toraja Arabica 500g',        150_000,    280_000],
                ['Kopi Flores Bajawa AAA 250g',     120_000,    200_000],
                ['Single Origin Aceh Gayo 200g',    130_000,    220_000],
                ['Matcha Grade A Ceremonial 100g',  200_000,    350_000],
                ['Green Tea Gyokuro Premium 100g',  180_000,    300_000],
                ['Dark Chocolate 85% Cacao 100g',    80_000,    140_000],
                ['Honey Manuka MGO400+ 500g',      350_000,    550_000],
                ['Olive Oil Extra Virgin Cold Press',200_000,   350_000],
                ['Sambal Matah Premium Artisan 250g', 45_000,    85_000],
                ['Tempeh Organik Kedelai Hitam',     30_000,     55_000],
                ['Beras Merah Organik 5kg',         120_000,    200_000],
                ['Himalayan Pink Salt 1kg',           80_000,    140_000],
                ['Granola Oat Almond Honey 500g',   120_000,    200_000],
                ['Chia Seeds Organik 500g',          90_000,    160_000],
                ['Almond Butter Smooth 350g',       130_000,    220_000],
                ['Protein Bar 12-Pack',             200_000,    350_000],
                ['Cold Brew Coffee Concentrate 1L', 100_000,    160_000],
                ['Kombucha Ginger Lemon 330ml',      30_000,     55_000],
                ['Coconut Milk Premium BPA-Free 400ml',18_000,   35_000],
                ['Kecap Manis Premium 620ml',        35_000,     60_000],
            ],
            'tags' => ['Food', 'Beverage', 'Organic'],
        ],

        // ── Books & Learning ─────────────────────────────────────────────────
        'Books' => [
            'emoji'    => ['📚','📖','📕','📗','📙','🎓','🧠','📝','🖊️','📰','📓','📄'],
            'products' => [
                ['Atomic Habits – James Clear',         89_000,    130_000],
                ['The Psychology of Money',              75_000,    110_000],
                ['Deep Work – Cal Newport',              80_000,    120_000],
                ['Zero to One – Peter Thiel',            85_000,    125_000],
                ['Thinking, Fast and Slow',             110_000,    160_000],
                ['Sapiens – Yuval Noah Harari',          95_000,    140_000],
                ['The Lean Startup',                     85_000,    125_000],
                ['Rich Dad Poor Dad',                    75_000,    115_000],
                ['Clean Code – Robert Martin',          150_000,    220_000],
                ['Designing Data-Intensive Applications',250_000,   380_000],
                ['The Pragmatic Programmer',            200_000,    300_000],
                ['You Don\'t Know JS (Bundle 6 Books)', 450_000,    650_000],
                ['Laravel: Up & Running 3rd Ed',        280_000,    400_000],
                ['Next.js in Action',                   220_000,    320_000],
                ['System Design Interview Vol.2',       300_000,    450_000],
                ['Buku Pelajaran Bahasa Indonesia SMA',  55_000,     85_000],
                ['Filosofi Teras – Henry Manampiring',   65_000,    100_000],
                ['Laskar Pelangi – Andrea Hirata',       55_000,     85_000],
                ['Pulang – Tere Liye',                   60_000,     95_000],
                ['Sejarah Indonesia Modern 1200-2008',   95_000,    145_000],
            ],
            'tags' => ['Books', 'Learning', 'Education'],
        ],

        // ── Home & Living ────────────────────────────────────────────────────
        'Home' => [
            'emoji'    => ['🪴','🛋️','🪑','🍳','🧹','💡','🪞','🛁','🪟','🧺','🕯️','🫙'],
            'products' => [
                ['Philips Hue Starter Kit E27',      1_500_000,  2_200_000],
                ['IKEA Kallax 4x4 Shelf Unit',       1_200_000,  1_700_000],
                ['Dyson V15 Detect Vacuum',          10_000_000, 13_000_000],
                ['Instant Pot Duo 7-in-1 6Qt',       3_000_000,  4_200_000],
                ['Tefal Air Fryer Easy Fry 4.2L',    1_200_000,  1_800_000],
                ['Philips HD3767 Rice Cooker 1.8L',    800_000,  1_200_000],
                ['Nespresso Vertuo Pop Coffee',       2_500_000,  3_500_000],
                ['Panasonic Front Load Washer 9kg',  6_500_000,  9_000_000],
                ['Sharp Refrigerator 2-Door 275L',   6_000_000,  8_000_000],
                ['Xiaomi Robot Vacuum Mop 3C',       3_500_000,  5_000_000],
                ['Lampu LED Smart RGB Strip 5m',       250_000,    450_000],
                ['Tanaman Monstera Deliciosa Besar',   500_000,    900_000],
                ['Pot Keramik Set 3pcs Nordic',       350_000,    600_000],
                ['Selimut Tencel Weighted 7kg',       800_000,  1_300_000],
                ['Bantal Tidur Memory Foam',           400_000,    700_000],
                ['Karpet Bulu Tebal 160x230cm',      1_200_000,  1_900_000],
                ['Lilin Aromaterapi Soy Wax Set 3',    250_000,    420_000],
                ['Diffuser Aroma Ultrasonic 500ml',   350_000,    580_000],
                ['Storage Ottoman Multi-function',     750_000,  1_200_000],
                ['Standing Desk Electric 140cm',     5_000_000,  7_500_000],
            ],
            'tags' => ['Home', 'Living', 'Interior'],
        ],

        // ── Health & Beauty ──────────────────────────────────────────────────
        'Health' => [
            'emoji'    => ['💊','🧴','💆','🩺','🫧','🌸','💅','🧖','🪥','🩹','💉','🧬'],
            'products' => [
                ['Vitamin C 1000mg Time Release 90s',   80_000,    130_000],
                ['Omega-3 Fish Oil 1200mg 100s',         90_000,    150_000],
                ['Magnesium Glycinate 400mg 120s',      120_000,    200_000],
                ['Collagen Peptide Marine 300g',        200_000,    350_000],
                ['Sunscreen SPF50+ PA++++ 50ml',        180_000,    280_000],
                ['Niacinamide 10% Serum 30ml',           90_000,    160_000],
                ['Hyaluronic Acid 2% Serum 30ml',        85_000,    150_000],
                ['Retinol 0.5% Night Cream 50ml',       150_000,    250_000],
                ['Facial Wash Salicylic Acid 100ml',     80_000,    130_000],
                ['Toner Centella Asiatica 200ml',        90_000,    160_000],
                ['Sheet Mask Snail Mucin 10pcs',        100_000,    180_000],
                ['Lip Balm SPF30 Beeswax Set 3',         75_000,    130_000],
                ['Electric Toothbrush Smart Timer',     450_000,    700_000],
                ['Water Flosser Cordless 200ml',        350_000,    550_000],
                ['Blood Pressure Monitor Omron',        600_000,    900_000],
                ['Pulse Oximeter Digital',              200_000,    350_000],
                ['Thermometer Infrared No-Touch',       200_000,    350_000],
                ['Hand Cream Intense Repair 75ml',       65_000,    110_000],
                ['Hair Growth Serum Rosemary 60ml',     120_000,    200_000],
                ['Deodorant Natural Aluminium-Free',     80_000,    130_000],
            ],
            'tags' => ['Health', 'Beauty', 'Wellness'],
        ],

        // ── Automotive ───────────────────────────────────────────────────────
        'Automotive' => [
            'emoji'    => ['🚗','🔧','🛞','⛽','🚘','🔦','🛻','🪛','🏎️','🛢️','🔩','🧰'],
            'products' => [
                ['Dashcam 4K Dual Sony IMX',        1_200_000,  1_800_000],
                ['Tire Inflator Cordless 150PSI',     350_000,    600_000],
                ['Car Vacuum Cleaner 120W',            250_000,    450_000],
                ['Car Air Purifier HEPA UV',           500_000,    800_000],
                ['Engine Oil Shell Helix Ultra 5L',   400_000,    650_000],
                ['OBD2 Bluetooth Scanner ELM327',     250_000,    450_000],
                ['Seat Cover Leather Premium Set',    800_000,  1_400_000],
                ['Car Dash Mount Magnetic MagSafe',   180_000,    320_000],
                ['Jump Starter 2000A LithiumPack',    800_000,  1_300_000],
                ['Car Wax Ceramic Coating 500ml',     350_000,    600_000],
                ['Floor Mat All Weather 4pcs Rubber', 300_000,    550_000],
                ['Steering Wheel Cover Microfiber',   150_000,    280_000],
                ['GPS Navigator 7" Android Auto',    1_500_000,  2_200_000],
                ['Car Subwoofer 12" 1000W',          1_200_000,  1_800_000],
                ['Bike Carrier Roof Rack 2-bike',    1_500_000,  2_200_000],
                ['Windshield Sun Shade Foldable',     120_000,    220_000],
                ['Tow Rope Recovery Strap 7T',        200_000,    350_000],
                ['LED Interior Ambient Lights 4pcs',  200_000,    380_000],
            ],
            'tags' => ['Automotive', 'Accessories', 'Parts'],
        ],
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Factory definition
    // ──────────────────────────────────────────────────────────────────────────

    public function definition(): array
    {
        // Pick a random category
        $categoryKey = $this->faker->randomElement(array_keys($this->catalogue));
        return $this->buildForCategory($categoryKey);
    }

    // ── State methods (use these for category-specific batches) ──────────────

    public function electronics(): static
    {
        return $this->state(fn () => $this->buildForCategory('Electronics'));
    }

    public function sports(): static
    {
        return $this->state(fn () => $this->buildForCategory('Sports'));
    }

    public function fashion(): static
    {
        return $this->state(fn () => $this->buildForCategory('Fashion'));
    }

    public function food(): static
    {
        return $this->state(fn () => $this->buildForCategory('Food'));
    }

    public function books(): static
    {
        return $this->state(fn () => $this->buildForCategory('Books'));
    }

    public function home(): static
    {
        return $this->state(fn () => $this->buildForCategory('Home'));
    }

    public function health(): static
    {
        return $this->state(fn () => $this->buildForCategory('Health'));
    }

    public function automotive(): static
    {
        return $this->state(fn () => $this->buildForCategory('Automotive'));
    }

    public function recommended(): static
    {
        return $this->state(fn () => [
            'is_recommended' => true,
            'rating'         => $this->faker->randomFloat(1, 4.5, 5.0),
            'rating_count'   => $this->faker->numberBetween(400, 2500),
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock' => $this->faker->numberBetween(1, 5)]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function buildForCategory(string $categoryKey): array
    {
        $cat  = $this->catalogue[$categoryKey];
        [$name, $minPrice, $maxPrice] = $this->faker->randomElement($cat['products']);

        // Add subtle price variation to avoid duplicates from the same template
        $priceVariant = $this->faker->numberBetween(0, 3);
        $multipliers  = [1.00, 1.05, 0.95, 1.10];
        $price        = (int) round($this->faker->numberBetween($minPrice, $maxPrice) * $multipliers[$priceVariant] / 1000) * 1000;

        $rating      = $this->faker->randomFloat(1, 3.5, 5.0);
        $ratingCount = $this->faker->numberBetween(12, 3000);
        $stock       = $this->faker->numberBetween(0, 250);

        // High-rated, popular products are more likely to be recommended
        $popularityScore = ($rating / 5.0) * 0.5 + (min($ratingCount, 1000) / 1000) * 0.5;
        $isRecommended   = $this->faker->boolean((int) ($popularityScore * 35));

        // Pick 1-3 tags from the category pool
        $tagPool = $cat['tags'];
        $tags    = $this->faker->randomElements($tagPool, $this->faker->numberBetween(1, count($tagPool)));

        return [
            'name'         => $name,
            'slug'         => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(10000, 99999),
            'category'     => $categoryKey,
            'price'        => $price,
            'rating'       => $rating,
            'rating_count' => $ratingCount,
            'emoji'        => $this->faker->randomElement($cat['emoji']),
            'description'  => $this->generateDescription($name, $categoryKey, $price),
            'stock'        => $stock,
            'tags'         => $tags,
            'is_recommended' => $isRecommended,
        ];
    }

    private function generateDescription(string $name, string $category, int $price): string
    {
        $priceFormatted = $price >= 1_000_000
            ? 'Rp ' . number_format($price / 1_000_000, 1) . 'jt'
            : 'Rp ' . number_format($price / 1000, 0) . 'rb';

        $descriptors = [
            'Electronics' => ['performa tinggi', 'teknologi terkini', 'desain premium', 'efisiensi daya'],
            'Sports'      => ['daya tahan luar biasa', 'ergonomis', 'performa optimal', 'material berkualitas'],
            'Fashion'     => ['desain elegan', 'bahan premium', 'tampilan stylish', 'nyaman dipakai'],
            'Food'        => ['cita rasa autentik', 'bahan alami pilihan', 'proses higienis', 'kandungan nutrisi tinggi'],
            'Books'       => ['wawasan mendalam', 'karya terbaik', 'ulasan kritis', 'perspektif baru'],
            'Home'        => ['kualitas tinggi', 'desain modern', 'tahan lama', 'mudah digunakan'],
            'Health'      => ['formula klinis', 'bahan alami terseleksi', 'uji klinis', 'standar farmasi'],
            'Automotive'  => ['presisi tinggi', 'material OEM grade', 'ketahanan optimal', 'performa andal'],
        ];

        $desc = $descriptors[$category] ?? ['kualitas terbaik', 'produk pilihan'];
        $attr = $this->faker->randomElement($desc);

        $templates = [
            "{$name} hadir dengan {$attr}, menjadikannya pilihan ideal bagi yang menginginkan pengalaman terbaik. Harga {$priceFormatted} untuk kualitas premium yang tak perlu diragukan.",
            "Rasakan perbedaan nyata dengan {$name}. Dirancang untuk {$attr} dan memberikan nilai terbaik di kelasnya. Tersedia dengan harga {$priceFormatted}.",
            "{$name} merupakan produk unggulan kategori {$category} dengan {$attr}. Dipilih oleh ribuan pelanggan puas. Dapatkan sekarang seharga {$priceFormatted}.",
        ];

        return $this->faker->randomElement($templates);
    }
}
