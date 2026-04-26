<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'years_of_experience',
        'bio',
        'location',
        'license_number',
        'license',
        'license_status',
        'commercial_reg',
        'commercial_reg_status',
        'rejection_reason',
        'user_id',
        'secretary_id',
        'medical_department_id',
        'profile_picture',
        'rating'
    ];

       public function user() // المالك
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }



    public function department()
    {
        return $this->belongsTo(MedicalDepartment::class, 'medical_department_id');
    }


    // داخل app/Models/Clinic.php

    /**
     * علاقة العيادة مع إعدادات الدوام الخاصة بها.
     * كل عيادة لها إعداد دوام واحد.
     */
    public function schedule()
    {
        return $this->hasOne(ClinicSchedule::class);
    }


    // داخل app/Models/Clinic.php

    /**
     * مواعيد العيادة.
     */
    public function appointments()
    {
        return $this->hasMany(ClinicAppointment::class);
    }

    /**
     * المرضى الذين زاروا العيادة (علاقة متعدد لمتعدد عبر المواعيد).
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'clinic_appointments')
                    ->withPivot(['appointment_date', 'status', 'type'])
                    ->withTimestamps();
    }

    /**
     * الوصول المباشر إلى أيام دوام العيادة (اختياري للسهولة).
     */
    public function workingDays()
    {
        return $this->hasManyThrough(
            Weekday::class,
            ClinicSchedule::class,
            'clinic_id',         // Foreign key on clinic_schedules table
            'id',                // Foreign key on weekdays table
            'id',                // Local key on clinics table
            'id'                 // Local key on clinic_schedules table
        )->join('clinic_schedule_days', 'weekdays.id', '=', 'clinic_schedule_days.weekday_id')
        ->where('clinic_schedule_days.clinic_schedule_id', '=', $this->schedule?->id);
    }
}
