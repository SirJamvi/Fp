<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'reservasi_id',
        'amount',
        'payment_type',
        'status',
        'deposit',
    ];

    /**
     * Get the reservation that owns the payment.
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }
}