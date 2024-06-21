<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'no_rekening',
        'pemilik',
        'image',
        'is_cash'
    ];

    protected $casts = [
        'is_cash' => 'boolean',
    ];
}
