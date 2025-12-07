<?php

namespace App\Models;

use App\Enums\OtpCodePurpose;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCodes extends Model
{
    use HasFactory;

    protected $table = 'otp_codes';

    protected $fillable = [
        'user_id',
        'otp_code',
        'expires_at',
        'is_used',
        'purpose',
    ];

    protected $casts = [
        'purpose' =>  OtpCodePurpose::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }
}
