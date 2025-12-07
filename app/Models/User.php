<?php

namespace App\Models;


use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login_at',
        'is_active',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class
    ];

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class , 'user_id' , 'id');
    }

    public function citizenProfile(): HasOne
    {
        return $this->hasOne(CitizenProfile::class , 'user_id' , 'id');
    }

    public function citizenComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class , 'citizen_id' , 'id');
    }

    public function officerComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class , 'assigned_officer_id' , 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class , 'uploaded_by' , 'id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class , 'actor_id' , 'id');
    }

    public function failedLogins(): HasMany
    {
        return $this->hasMany(FailedLogin::class , 'user_id' , 'id');
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCodes::class , 'user_id' , 'id');
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class , 'user_id' , 'id');
    }
}
