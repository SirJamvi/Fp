<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'is_available',
        'preparation_time',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'preparation_time' => 'integer',
    ];

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
}