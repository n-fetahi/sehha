<?php
// app/Models/ClinicAppointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Class ClinicAppointment
 * @package App\Models
 * @property int $id المعرف
 * @property int $clinic_id معرف العيادة
 * @property int $patient_id معرف المريض
 * @property string $status حالة الحجز
 * @property string $type نوع الحجز (استشارة / متابعة)
 * @property string $appointment_date تاريخ الحجز
 * @property string $appointment_time وقت الحجز
 * @property string|null $follow_up_date تاريخ المتابعة الأساسي
 * @property int|null $follow_up_period فترة المتابعة بالأيام
 * @property string|null $diagnosis التشخيص
 * @property string|null $medications الأدوية
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read Clinic $clinic العيادة المرتبطة
 * @property-read Patient $patient المريض المرتبط
 */
class ClinicAppointment extends Model
{
    // ثوابت حالات الحجز
    const STATUS_PENDING_BOOKING = 'pending_booking';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_WAITING = 'waiting';
    const STATUS_NO_SHOW = 'no_show';
    const STATUS_CANCELLED = 'cancelled';

    // ثوابت أنواع الحجز
    const TYPE_CONSULTATION = 'consultation';
    const TYPE_FOLLOW_UP = 'follow_up';

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'previous_appointment_id',
        'wallet_id',
        'status',
        'type',
        'appointment_date',
        'appointment_time',
        'booking_fee',
        'follow_up_date',
        'follow_up_period',
        'diagnosis',
        'medications',
        'rejection_reason',
        'cancelled_reason'

    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'follow_up_date' => 'date',
        'follow_up_period' => 'integer',
    ];

    /**
     * Boot method: يتم تنفيذها عند إنشاء أو تحديث النموذج.
     */
    protected static function booted()
    {
        static::creating(function ($appointment) {
            // إذا كانت فترة المتابعة غير محددة، نأخذ القيمة الافتراضية من إعدادات العيادة
            if (empty($appointment->follow_up_period)) {
                $clinicSchedule = ClinicSchedule::where('clinic_id', $appointment->clinic_id)->first();
                if ($clinicSchedule) {
                    $appointment->follow_up_period = $clinicSchedule->follow_up_period;
                }
            }
        });
    }

    /**
     * العلاقة مع العيادة.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * العلاقة مع المريض.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * العلاقة مع المحفظة الإلكترونية المستخدمة في الدفع.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }


    public function examinationRequests()
    {
        return $this->hasMany(ExaminationRequest::class);
    }

    // أضف هذه العلاقات
    public function previousAppointment(): BelongsTo
    {
        return $this->belongsTo(ClinicAppointment::class, 'previous_appointment_id');
    }

    public function nextAppointment(): HasOne
    {
        return $this->hasOne(ClinicAppointment::class, 'previous_appointment_id');
    }

    // ============================================
    //          دوال مساعدة (Scopes)
    // ============================================

    /**
     * Scope: مواعيد تاريخ معين.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    /**
     * Scope: مواعيد عيادة معينة.
     */
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope: مواعيد بحالة معينة.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: مواعيد قادمة (من اليوم فصاعداً).
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('appointment_date', '>=', now()->toDateString());
    }

    // ============================================
    //          دوال مساعدة (Accessors)
    // ============================================

    /**
     * الحصول على اسم حالة الحجز بالعربية.
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING_BOOKING => 'انتظار الحجز',
            self::STATUS_PENDING => 'معلق',
            self::STATUS_APPROVED => 'مقبول',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_WAITING => 'انتظار الحضور',
            self::STATUS_NO_SHOW => 'لم يتم الحضور',
            self::STATUS_CANCELLED => 'ملغي',
            default => $this->status,
        };
    }

    /**
     * الحصول على اسم نوع الحجز بالعربية.
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CONSULTATION => 'استشارة',
            self::TYPE_FOLLOW_UP => 'متابعة',
            default => $this->type,
        };
    }

    /**
     * حساب وقت انتهاء الموعد بناءً على نوع الحجز ومدة الجلسة.
     */
    public function getEndTimeAttribute(): ?string
    {
        if (!$this->appointment_time) {
            return null;
        }

        $schedule = $this->clinic->schedule;
        if (!$schedule) {
            return is_string($this->appointment_time)
                ? $this->appointment_time
                : $this->appointment_time->format('H:i');
        }

        $duration = $this->type === self::TYPE_CONSULTATION
            ? $schedule->consultation_duration
            : $schedule->follow_up_duration;

        return \Carbon\Carbon::parse($this->appointment_time)
            ->addMinutes($duration)
            ->format('H:i');
    }
}
