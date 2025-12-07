<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitizenProfile extends Model
{
    use HasFactory;

    protected $table = 'citizen_profiles';

    protected $fillable = [
        'user_id',
        'phone',
        'profile_picture',
        'national_number',
        'city',
        'address'
    ];

    protected $casts = [
        'city' => CitizenProfile::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }
}
