<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser; // أضف هذا السطر
use Filament\Panel; // أضف هذا السطر

class User extends Authenticatable implements FilamentUser // أضف implements هنا
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'user_type',
        'gender',  // الحقل الجديد
        'user_status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // علاقة المريض
    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

       public function ownedClinic()
    {
        return $this->hasOne(Clinic::class, 'user_id');
    }

       public function ownedLab()
    {
        return $this->hasOne(Lab::class, 'user_id');
    }

    public function secretaryClinic()
    {
        return $this->hasOne(Clinic::class, 'secretary_id');
    }


    /**
     * إشعارات التطبيق الخاصة بالمستخدم.
     */
    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    // Helpers
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isPatient(): bool
    {
        return $this->user_type === 'patient';
    }

    public function isClinic(): bool
    {
        return $this->user_type === 'clinic';
    }

    public function isLab(): bool
    {
        return $this->user_type === 'lab';
    }

    public function isSecretary(): bool
    {
        return $this->user_type === 'secretary';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // أو return str_ends_with($this->email, '@example.com');
    }
}
