<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function orders()
    {
        return $this->hasMany(Order::class, 'menu_id', 'id');
    }

    public function transactions()
    {
    return $this->hasMany(Transaksi::class, 'menu_id', 'id');
    }

}