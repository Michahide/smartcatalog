<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->unsignedBigInteger('price');
            $table->float('rating', 2, 1)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('emoji', 10)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->json('tags')->nullable();
            $table->boolean('is_recommended')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
