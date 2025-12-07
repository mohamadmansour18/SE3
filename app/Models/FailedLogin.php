<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedLogin extends Model
{
    use HasFactory;

    protected $table = 'failed_logins';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'occurred_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id');
    }
}
