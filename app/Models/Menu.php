<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'is_available',
        'preparation_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'preparation_time' => 'integer',
    ];

    /**
     * Get the category options.
     *
     * @return array
     */
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
}