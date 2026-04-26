<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // ✅ الحقول القابلة للتعبئة
    protected $fillable = [
        'birth_date',
        'profile_picture',
        'blood_type',
        'height',
        'weight',
        'user_id',
    ];

    // 🔄 تحويل أنواع البيانات تلقائياً
    protected $casts = [
        'birth_date'  => 'date',
        'height'      => 'decimal:2',
        'weight'      => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // ============================================
    //              العلاقات (Relations)
    // ============================================

    /**
     * علاقة عكسية 1:1 مع جدول المستخدمين
     * كل مريض ينتمي لمستخدم واحد
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // داخل app/Models/Patient.php

    /**
     * مواعيد المريض في العيادات.
     */
    public function clinicAppointments()
    {
        return $this->hasMany(ClinicAppointment::class);
    }

    /**
     * حجوزات المختبرات الخاصة بالمريض.
     */
    public function labAppointments()
    {
        return $this->hasMany(LabAppointment::class);
    }

    /**
     * العيادات التي زارها المريض.
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_appointments')
                    ->withPivot(['appointment_date', 'status', 'type', 'diagnosis'])
                    ->withTimestamps();
    }

    public function examinationRequests()
    {
        return $this->hasManyThrough(
            ExaminationRequest::class,
            ClinicAppointment::class,
            'patient_id',         // Foreign key on clinic_appointments
            'clinic_appointment_id', // Foreign key on examination_requests
            'id',                 // Local key on patients
            'id'                  // Local key on clinic_appointments
        );
    }

    // ============================================
    //          دوال مساعدة (Helpers)
    // ============================================

    /**
     * حساب العمر من تاريخ الميلاد
     */
    public function getAgeAttribute(): int
    {
        return $this->birth_date->age;
    }


}
