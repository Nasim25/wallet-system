<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'agreement_token',
        'masked_number',
        'balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function credit(float $amount, string $trxId, $paymentId = null): Transaction
    {
        return DB::transaction(function () use ($amount, $trxId, $paymentId) {
            $this->increment('balance', $amount);
            $this->refresh();

            return $this->transactions()->create([
                'trx_id' => $trxId,
                'payment_id' => $paymentId,
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $this->balance,
                'status' => 'completed',
            ]);
        });
    }

    // Deduct amount from wallet
    public function debit(float $amount, string $trxId): Transaction
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        return DB::transaction(function () use ($amount, $trxId) {
            $this->decrement('balance', $amount);
            $this->refresh();

            return $this->transactions()->create([
                'trx_id' => $trxId,
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $this->balance,
                'status' => 'completed',
            ]);
        });
    }
}
