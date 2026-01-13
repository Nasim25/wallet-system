<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'agreement_token',
        'masked_account',
        'balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];
}
