<?php
// app/Models/ClinicScheduleDay.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ClinicScheduleDay
 * @package App\Models
 * @property int $id المعرف
 * @property int $clinic_schedule_id معرف اعدادات الحجوزات للعيادة
 * @property int $weekday_id معرف اليوم
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read ClinicSchedule $clinicSchedule إعدادات العيادة
 * @property-read Weekday $weekday اليوم
 */
class ClinicScheduleDay extends Model
{
    protected $fillable = [
        'clinic_schedule_id',
        'weekday_id',
    ];

    /**
     * العلاقة مع إعدادات العيادة.
     */
    public function clinicSchedule(): BelongsTo
    {
        return $this->belongsTo(ClinicSchedule::class);
    }

    /**
     * العلاقة مع اليوم.
     */
    public function weekday(): BelongsTo
    {
        return $this->belongsTo(Weekday::class);
    }
}
