<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'category', 'price',
        'rating', 'rating_count', 'emoji',
        'description', 'stock', 'tags', 'is_recommended',
    ];

    protected $casts = [
        'tags'           => 'array',
        'is_recommended' => 'boolean',
        'rating'         => 'float',
        'price'          => 'integer',
        'stock'          => 'integer',
        'rating_count'   => 'integer',
    ];

    protected $appends = ['price_formatted'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function getPriceFormattedAttribute(): string
    {
        if ($this->price >= 1_000_000) {
            return 'Rp ' . number_format($this->price / 1_000_000, 1) . 'jt';
        }
        return 'Rp ' . number_format($this->price / 1000, 0) . 'rb';
    }
}
