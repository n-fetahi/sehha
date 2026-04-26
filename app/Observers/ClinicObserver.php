<?php
//sehha\app\Observers\ClinicObserver.php
namespace App\Observers;

use App\Models\Clinic;
use App\Models\ClinicSchedule;

class ClinicObserver
{
    /**
     * يتم تشغيله تلقائيًا عند إنشاء عيادة جديدة
     */
    public function created(Clinic $clinic): void
    {
        ClinicSchedule::create([
            'clinic_id' => $clinic->id,
            'start_time' => '08:00:00',
            'end_time' => '14:00:00',
            'consultation_duration' => 15,
            'follow_up_duration' => 10,
            'follow_up_period' => 7,
            'booking_fee' => 500,
            'is_available' => 1,
        ]);
    }
}