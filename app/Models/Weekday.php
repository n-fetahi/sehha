<?php
// app/Models/Weekday.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Weekday
 * @package App\Models
 * @property int $id المعرف
 * @property string $name اسم اليوم بالإنجليزية
 * @property int $order ترتيب اليوم (1 = السبت, 7 = الجمعة)
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|LabSchedule[] $labSchedules إعدادات المختبرات المرتبطة
 */
class Weekday extends Model
{
    protected $fillable = ['name', 'order'];

    /**
     * العلاقة مع إعدادات دوام المختبرات.
     */
    public function labSchedules(): BelongsToMany
    {
        return $this->belongsToMany(LabSchedule::class, 'lab_schedule_days')
                    ->withTimestamps();
    }

    /**
     * العلاقة مع إعدادات دوام العيادات.
     */
    public function clinicSchedules(): BelongsToMany
    {
        return $this->belongsToMany(ClinicSchedule::class, 'clinic_schedule_days')
                    ->withTimestamps();
    }
}
