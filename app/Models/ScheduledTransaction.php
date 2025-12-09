<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledTransaction extends Model
{
    use HasFactory;

    protected $table = 'scheduled_transactions';

    protected $fillable = [
        'account_id',
        'to_account_id',
        'type',
        'amount',
        'frequency',
        'next_run_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'amount'=> 'decimal:2',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
