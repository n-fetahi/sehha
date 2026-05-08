<?php

namespace App\Console\Commands;

use App\Models\ClinicAppointment;
use Illuminate\Console\Command;

class AutoUpdateAppointmentStatus extends Command
{
    protected $signature = 'appointments:auto-status';

    protected $description = 'تحديث حالة الحجوزات تلقائياً: waiting عند دخول وقت الحجز، no_show عند انتهاء الدوام';

    public function handle(): int
    {
        $now = now();

        $appointments = ClinicAppointment::whereDate('appointment_date', $now->toDateString())
            ->whereIn('status', [
                ClinicAppointment::STATUS_APPROVED,
                ClinicAppointment::STATUS_WAITING,
            ])
            ->whereNotNull('appointment_time')
            ->with('clinic.schedule')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('لا توجد حجوزات تحتاج تحديث');
            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($appointments as $appointment) {
            $schedule = $appointment->clinic?->schedule;

            if (!$schedule || !$schedule->end_time) {
                continue;
            }

            $appointmentTime = $appointment->appointment_time;
            $endTime = $schedule->end_time;

            if ($now >= $appointmentTime && $now < $endTime) {
                if ($appointment->status === ClinicAppointment::STATUS_APPROVED) {
                    $appointment->update(['status' => ClinicAppointment::STATUS_WAITING]);
                    $this->info("الحجز #{$appointment->id}: مقبول → انتظار الحضور");
                    $updated++;
                }
            } elseif ($now >= $endTime) {
                if ($appointment->status !== ClinicAppointment::STATUS_NO_SHOW) {
                    $appointment->update(['status' => ClinicAppointment::STATUS_NO_SHOW]);
                    $this->info("الحجز #{$appointment->id}: {$appointment->status} → لم يتم الحضور");
                    $updated++;
                }
            }
        }

        $this->info("تم تحديث {$updated} حجز/حجوزات");

        return self::SUCCESS;
    }
}
