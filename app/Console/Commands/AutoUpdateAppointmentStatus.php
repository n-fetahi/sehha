<?php

namespace App\Console\Commands;

use App\Models\ClinicAppointment;
use App\Services\AppNotificationService;
use Illuminate\Console\Command;

class AutoUpdateAppointmentStatus extends Command
{
    protected $signature = 'appointments:auto-status';

    protected $description = 'تحديث حالة الحجوزات تلقائياً: waiting عند دخول وقت الحجز، no_show عند انتهاء الدوام';

    public function handle(): int
    {
        $now = now();
        $today = $now->toDateString();

        $appointments = ClinicAppointment::whereNotNull('appointment_date')
            ->whereDate('appointment_date', '<=', $today)
            ->whereIn('status', [
                ClinicAppointment::STATUS_PENDING,
                ClinicAppointment::STATUS_APPROVED,
                ClinicAppointment::STATUS_WAITING,
            ])
            ->with(['clinic.schedule', 'patient.user'])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('لا توجد حجوزات تحتاج تحديث');
            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($appointments as $appointment) {
            $schedule = $appointment->clinic?->schedule;

            if ($appointment->appointment_date->lessThan($today)) {
                $appointment->update(['status' => ClinicAppointment::STATUS_NO_SHOW]);
                AppNotificationService::appointmentNoShow($appointment);
                $this->info("الحجز #{$appointment->id}: {$appointment->status} (تاريخ {$appointment->appointment_date->toDateString()}) → لم يتم الحضور");
                $updated++;
                continue;
            }

            if (!$appointment->appointment_time || !$schedule || !$schedule->end_time) {
                continue;
            }

            $appointmentTime = $appointment->appointment_time;
            $endTime = $schedule->end_time;

            if ($now >= $appointmentTime && $now < $endTime) {
                if ($appointment->status !== ClinicAppointment::STATUS_WAITING) {
                    $appointment->update(['status' => ClinicAppointment::STATUS_WAITING]);
                    AppNotificationService::appointmentWaiting($appointment);
                    $this->info("الحجز #{$appointment->id}: {$appointment->status} → انتظار الحضور");
                    $updated++;
                }
            } elseif ($now >= $endTime) {
                if ($appointment->status !== ClinicAppointment::STATUS_NO_SHOW) {
                    $appointment->update(['status' => ClinicAppointment::STATUS_NO_SHOW]);
                    AppNotificationService::appointmentNoShow($appointment);
                    $this->info("الحجز #{$appointment->id}: {$appointment->status} → لم يتم الحضور");
                    $updated++;
                }
            }
        }

        $this->info("تم تحديث {$updated} حجز/حجوزات");

        return self::SUCCESS;
    }
}
