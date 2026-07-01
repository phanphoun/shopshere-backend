<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED    = 'shipped';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_CANCELLED  = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID   = 'paid';

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'tax',
        'shipping_fee',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'shipping_address',
        'phone',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'shipping_fee'  => 'decimal:2',
        'discount'      => 'decimal:2',
        'total'         => 'decimal:2',
        'shipped_at'    => 'datetime',
        'delivered_at'  => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Boot                                                                */
    /* ------------------------------------------------------------------ */

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                       */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                           */
    /* ------------------------------------------------------------------ */

    /**
     * Return the Bootstrap CSS class for the current order status.
     */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'bg-warning text-dark',
            self::STATUS_PROCESSING => 'bg-info',
            self::STATUS_SHIPPED    => 'bg-primary',
            self::STATUS_DELIVERED  => 'bg-success',
            self::STATUS_CANCELLED  => 'bg-danger',
            default                 => 'bg-secondary',
        };
    }

    /**
     * Return the Bootstrap CSS class for the current payment status.
     */
    public function getPaymentClassAttribute(): string
    {
        return $this->payment_status === self::PAYMENT_PAID
            ? 'bg-success'
            : 'bg-warning text-dark';
    }

    public function isPending(): bool    { return $this->status === self::STATUS_PENDING; }
    public function isProcessing(): bool { return $this->status === self::STATUS_PROCESSING; }
    public function isShipped(): bool    { return $this->status === self::STATUS_SHIPPED; }
    public function isDelivered(): bool  { return $this->status === self::STATUS_DELIVERED; }
    public function isCancelled(): bool  { return $this->status === self::STATUS_CANCELLED; }

    public function isPaid(): bool       { return $this->payment_status === self::PAYMENT_PAID; }
}
