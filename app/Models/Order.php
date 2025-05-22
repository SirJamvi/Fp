<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'reservasi_id',
        'menu_id',
        'user_id',
        'quantity',
        'price_at_order',
        'total_price',
        'notes',
        'status',
    ];

    /**
     * Event that runs after an order is successfully created.
     * Used to automatically save data to the transactions table.
     */
    protected static function booted()
    {
        static::created(function ($order) {
            // Make sure the menu relationship is available
            $menu = $order->menu()->first();

            Transaction::create([
                'reservasi_id' => $order->reservasi_id,
                'menu_id'      => $order->menu_id,
                'item_name'    => $menu ? $menu->name : 'Unknown',
                'quantity'     => $order->quantity,
                'total_price'  => $order->total_price,
                'status'       => 'belum dibayar',
            ]);
        });
    }

    // Relationship to Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    // Relationship to User (customer or waiter)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relationship to Reservasi
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }
}