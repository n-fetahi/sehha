<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LabAppointment
 * @package App\Models
 * @property int $id المعرف
 * @property int $lab_id معرف المختبر
 * @property int|null $clinic_appointment_id معرف حجز العيادة (اختياري)
 * @property int $patient_id معرف المريض
 * @property string $status حالة الحجز (booked, completed)
 * @property string|null $result مسار ملف نتيجة الفحوصات PDF
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read Lab $lab المختبر
 * @property-read ClinicAppointment|null $clinicAppointment حجز العيادة المرتبط
 * @property-read Patient $patient المريض
 */
class LabAppointment extends Model
{
    // ثوابت الحالات
    const STATUS_BOOKED = 'booked';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'lab_id',
        'patient_id',
        'status',
        'result',
    ];

    protected $casts = [
        'clinic_appointment_id' => 'integer',
    ];

    /**
     * العلاقة مع المختبر.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }



    /**
     * العلاقة مع المريض.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }


    public function examinationRequests()
    {
        return $this->hasMany(ExaminationRequest::class);
    }

    // ============================================
    //          دوال مساعدة (Accessors)
    // ============================================

    /**
     * الحصول على اسم الحالة بالعربية.
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_BOOKED => 'تم الحجز',
            self::STATUS_COMPLETED => 'مكتمل',
            default => $this->status,
        };
    }

    /**
     * هل تم رفع نتيجة الفحص؟
     */
    public function hasResult(): bool
    {
        return !empty($this->result);
    }
}
