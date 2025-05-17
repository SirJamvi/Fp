<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'reservasi_id',
        'menu_id',
        'item_name',
        'quantity',
        'total_price',
        'status',
    ];

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
