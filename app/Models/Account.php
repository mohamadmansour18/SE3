<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'user_id',
        'account_number',
        'account_type',
        'status',
        'balance',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'balance' =>  'decimal:2',
    ];

    // صاحب الحساب
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' ,  'id');
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_account_id' , 'id');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id' , 'id');
    }

    public function scheduledTransactionsFrom(): HasMany
    {
        return $this->hasMany(ScheduledTransaction::class, 'account_id' , 'id');
    }


    public function scheduledTransactionsTo(): HasMany
    {
        return $this->hasMany(ScheduledTransaction::class, 'to_account_id' , 'id');
    }
}
