<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo user ────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'michael@dipa.co.id'],
            [
                'name'     => 'Michael',
                'password' => Hash::make('password'),
            ]
        );

        // ── Products — delegated to ProductSeeder ────────────────────────────
        $this->call(ProductSeeder::class);
    }
}
