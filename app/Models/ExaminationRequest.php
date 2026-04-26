<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationRequest extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'clinic_appointment_id',
        'lab_appointment_id',
        'examination_item_id',
        'status',
    ];

    protected $casts = [
        'clinic_appointment_id' => 'integer',
        'lab_appointment_id' => 'integer',
    ];

    // ========== العلاقات ==========

    public function clinicAppointment(): BelongsTo
    {
        return $this->belongsTo(ClinicAppointment::class);
    }

    public function labAppointment(): BelongsTo
    {
        return $this->belongsTo(LabAppointment::class);
    }

    public function examinationItem(): BelongsTo
    {
        return $this->belongsTo(ExaminationItem::class);
    }

    // ========== دوال مساعدة ==========

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_IN_PROGRESS => 'قيد التنفيذ',
            self::STATUS_COMPLETED => 'مكتمل',
            default => $this->status,
        };
    }

    public function isFromClinic(): bool
    {
        return !is_null($this->clinic_appointment_id);
    }

    public function hasLabBooking(): bool
    {
        return !is_null($this->lab_appointment_id);
    }

    // ========== Scopes ==========

    public function scopeFromClinic($query)
    {
        return $query->whereNotNull('clinic_appointment_id');
    }

    public function scopeFromLab($query)
    {
        return $query->whereNotNull('lab_appointment_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
