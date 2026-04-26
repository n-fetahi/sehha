<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    // الحقول اللي يسمح للتعديل عليها (fillable)
    protected $fillable = [
        'name',               // اسم المختبر
        'phone',              // رقم الهاتف للمختبر
        'medical_director',   // المسؤول الطبي
        'location',           // موقع المختبر
        'license_number',     // رقم الترخيص الطبي
        'license',            // مسار ملف الترخيص (صورة أو PDF)
        'license_status',     // حالة الترخيص: pending / approved / rejected
        'commercial_reg',     // مسار السجل التجاري (صورة أو PDF)
        'commercial_reg_status',
        'rejection_reason',   // سبب الرفض إذا رفض المختبر
        'user_id',            // معرف المستخدم المرتبط
        'profile_picture',    // صورة المختبر (اختياري)
        'rating'
    ];

    /**
     * العلاقة مع المستخدم
     * كل مختبر مرتبط بمستخدم واحد
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * عناصر الفحوصات التي يقدمها هذا المختبر.
     */
    public function examinationItems()
    {
        return $this->belongsToMany(ExaminationItem::class, 'lab_examination_items')
                    ->withTimestamps();
    }

    /**
     * علاقة المختبر مع إعدادات الدوام الخاصة به.
     * كل مختبر له إعداد دوام واحد.
     */
    public function schedule()
    {
        return $this->hasOne(LabSchedule::class);
    }

    /**
     * حجوزات المختبر.
     */
    public function appointments()
    {
        return $this->hasMany(LabAppointment::class);
    }

    /**
     * الوصول المباشر إلى أيام دوام المختبر (اختياري للسهولة).
     * من خلال الاستدعاء $lab->schedule->weekdays
     */
    public function workingDays()
    {
        return $this->hasManyThrough(
            Weekday::class,
            LabSchedule::class,
            'lab_id',           // Foreign key on lab_schedules table
            'id',               // Foreign key on weekdays table
            'id',               // Local key on labs table
            'id'                // Local key on lab_schedules table
        )->join('lab_schedule_days', 'weekdays.id', '=', 'lab_schedule_days.weekday_id')
         ->where('lab_schedule_days.lab_schedule_id', '=', $this->schedule?->id);
    }

}
