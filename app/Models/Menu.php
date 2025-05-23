<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Tambahkan ini jika belum ada

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'name',
        'description',
        'price',
        'discount_percentage',
        'discounted_price',
        'image',
        'category',
        'is_available',
        'preparation_time',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'is_available' => 'boolean',
        'preparation_time' => 'integer',
    ];

    // Boot method untuk menghitung discounted_price secara otomatis
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($menu) {
            if ($menu->discount_percentage > 0 && $menu->price > 0) {
                $discountAmount = $menu->price * ($menu->discount_percentage / 100);
                $menu->discounted_price = $menu->price - $discountAmount;
            } else {
                $menu->discounted_price = $menu->price; // Jika tidak ada diskon, harga diskon sama dengan harga asli
            }
        });
    }

    public static function getCategoryOptions(): array
    {
        return [
            'food' => 'Food',
            'beverage' => 'Beverage',
            'dessert' => 'Dessert',
            'appetizer' => 'Appetizer',
            'other' => 'Other',
        ];
    }

    /**
     * Get the full URL for the menu image
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Pastikan menggunakan asset() untuk URL publik
            return asset('storage/' . $this->image);
        }
        // Gambar default jika tidak ada
        return asset('assets/img/default-food.png');
    }

    /**
     * Accessor for the final price (after discount)
     * Ini akan selalu mengembalikan harga yang harus dibayar pelanggan.
     */
    public function getFinalPriceAttribute()
    {
        // Gunakan discounted_price yang sudah dihitung dan disimpan di database
        return $this->discounted_price;
    }
}