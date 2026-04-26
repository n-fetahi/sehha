<?php

// app/Models/LabScheduleDay.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LabScheduleDay
 * @package App\Models
 * @property int $id المعرف
 * @property int $lab_schedule_id معرف اعدادات الحجوزات للمختبر
 * @property int $weekday_id معرف اليوم
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read LabSchedule $labSchedule إعدادات المختبر
 * @property-read Weekday $weekday اليوم
 */
class LabScheduleDay extends Model
{
    protected $fillable = [
        'lab_schedule_id',
        'weekday_id',
    ];

    /**
     * العلاقة مع إعدادات المختبر.
     */
    public function labSchedule(): BelongsTo
    {
        return $this->belongsTo(LabSchedule::class);
    }

    /**
     * العلاقة مع اليوم.
     */
    public function weekday(): BelongsTo
    {
        return $this->belongsTo(Weekday::class);
    }
}
