<?php

// app/Models/LabSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class LabSchedule
 * @package App\Models
 * @property int $id المعرف
 * @property int $lab_id معرف المختبر
 * @property string $start_time وقت بداية الدوام
 * @property string $end_time وقت نهاية الدوام
 * @property bool $is_available هل المختبر متاح للحجوزات
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read Lab $lab المختبر المرتبط
 * @property-read \Illuminate\Database\Eloquent\Collection|Weekday[] $weekdays أيام الدوام المحددة
 */
class LabSchedule extends Model
{
    protected $fillable = [
        'lab_id',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * العلاقة مع المختبر.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * العلاقة مع أيام الأسبوع (عبر الجدول الوسيط lab_schedule_days).
     */
    public function weekdays(): BelongsToMany
    {
        return $this->belongsToMany(Weekday::class, 'lab_schedule_days')
                    ->withTimestamps();
    }
}
