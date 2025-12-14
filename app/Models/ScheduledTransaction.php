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
        'name' ,
        'type',
        'amount',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'amount'=> 'decimal:2',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

}
