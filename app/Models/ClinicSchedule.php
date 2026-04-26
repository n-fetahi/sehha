<?php
// app/Models/ClinicSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class ClinicSchedule
 * @package App\Models
 * @property int $id المعرف
 * @property int $clinic_id معرف العيادة
 * @property string $start_time وقت بداية الدوام
 * @property string $end_time وقت نهاية الدوام
 * @property int $consultation_duration مدة الاستشارة بالدقائق
 * @property int $follow_up_duration مدة المتابعة بالدقائق
 * @property int $follow_up_period فترة المتابعة بالأيام
 * @property float $booking_fee مبلغ الحجز
 * @property bool $is_available هل العيادة متاحة للحجوزات
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read Clinic $clinic العيادة المرتبطة
 * @property-read \Illuminate\Database\Eloquent\Collection|Weekday[] $weekdays أيام الدوام المحددة
 */
class ClinicSchedule extends Model
{
    protected $fillable = [
        'clinic_id',
        'start_time',
        'end_time',
        'session_duration',
        'follow_up_period',
        'booking_fee',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'booking_fee' => 'decimal:2',
        'consultation_duration' => 'integer',
        'follow_up_duration' => 'integer',
        'follow_up_period' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * العلاقة مع العيادة.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * العلاقة مع أيام الأسبوع (عبر الجدول الوسيط clinic_schedule_days).
     */
    public function weekdays(): BelongsToMany
    {
        return $this->belongsToMany(Weekday::class, 'clinic_schedule_days')
                    ->withTimestamps();
    }
}
