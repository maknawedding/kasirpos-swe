<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'no_tenant',
        'birthday',
        'phone',
        'total_price',
        'notes',
        'payment_method_id',
        'paid_amount',
        'promo',
        'change_amount',
        'issued_date',
        'due_date',
        'paid_date',
        'keterangan',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function calculateTotalPrice(): float
    {
        $totalPrice = 0;
        foreach ($this->items as $item) {
            $totalPrice += $item->quantity * $item->unit_price;
        }
        return $totalPrice;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
