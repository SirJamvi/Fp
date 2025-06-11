<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Pengguna;

class CustomerNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservasi_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'scheduled_at',
        'sent_at',
        'is_sent'
    ];

    protected $casts = [
        'data'         => 'array',
        'read_at'      => 'datetime',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
        'is_sent'      => 'boolean',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopePending($query)
    {
        return $query->where('is_sent', false)
                     ->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '<=', now());
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsSent()
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }

    // Tambahkan type baru untuk notifikasi langsung saat reservasi dibuat
    const TYPE_RESERVATION_CREATED   = 'reservation_created';
    const TYPE_RESERVATION_CONFIRMED = 'reservation_confirmed';
    const TYPE_REMINDER_12_HOURS     = 'reminder_12_hours';
    const TYPE_REMINDER_1_HOUR       = 'reminder_1_hour';
    const TYPE_REMINDER_5_MINUTES    = 'reminder_5_minutes';
    const TYPE_PAYMENT_SUCCESS       = 'payment_success';
    const TYPE_RESERVATION_CANCELLED = 'reservation_cancelled';
}