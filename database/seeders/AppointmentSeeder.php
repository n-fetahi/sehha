<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ClinicAppointment;
use App\Models\ExaminationItem;
use App\Models\ExaminationRequest;
use App\Models\Lab;
use App\Models\LabAppointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::orderBy('id')->get();
        $clinics = Clinic::with('schedule')->orderBy('id')->get();
        $labs = Lab::orderBy('id')->get();
        $examinationItems = ExaminationItem::orderBy('id')->get();

        if ($patients->isEmpty() || $clinics->isEmpty() || $labs->isEmpty() || $examinationItems->isEmpty()) {
            return;
        }

        $clinicStatuses = [
            ClinicAppointment::STATUS_APPROVED,
            ClinicAppointment::STATUS_COMPLETED,
            ClinicAppointment::STATUS_WAITING,
            ClinicAppointment::STATUS_PENDING,
            ClinicAppointment::STATUS_CANCELLED,
        ];

        $diagnoses = [
            'أعراض التهاب عام مع طلب فحوصات داعمة للتشخيص.',
            'متابعة حالة مزمنة وتقييم المؤشرات المخبرية.',
            'اشتباه نقص فيتامينات ومعادن مع إرهاق متكرر.',
            'مراجعة نتائج سابقة وتحديد خطة متابعة.',
            'أعراض تحتاج إلى استبعاد عدوى أو اضطراب هرموني.',
        ];

        $medications = [
            'مسكن عند اللزوم مع الإكثار من السوائل.',
            'مكملات غذائية حسب نتيجة الفحوصات.',
            'خطة علاجية مؤقتة حتى ظهور نتائج المختبر.',
            null,
            'إرشادات غذائية ومتابعة بعد أسبوعين.',
        ];

        $createdClinicAppointments = collect();

        foreach ($clinics->take(15)->values() as $index => $clinic) {
            $patient = $patients[$index % $patients->count()];
            $schedule = $clinic->schedule;
            $appointmentDate = Carbon::today()->addDays($index + 1)->toDateString();
            $appointmentTime = $schedule?->start_time
                ? Carbon::parse($schedule->start_time)->addMinutes(($index % 4) * 30)->format('H:i:s')
                : Carbon::createFromTime(9 + ($index % 5), 0)->format('H:i:s');
            $status = $clinicStatuses[$index % count($clinicStatuses)];
            $type = $index % 4 === 0
                ? ClinicAppointment::TYPE_FOLLOW_UP
                : ClinicAppointment::TYPE_CONSULTATION;

            $appointment = ClinicAppointment::updateOrCreate(
                [
                    'clinic_id' => $clinic->id,
                    'patient_id' => $patient->id,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                ],
                [
                    'status' => $status,
                    'type' => $type,
                    'booking_fee' => $schedule?->booking_fee ?? 0,
                    'follow_up_date' => $status === ClinicAppointment::STATUS_COMPLETED
                        ? Carbon::parse($appointmentDate)->addDays($schedule?->follow_up_period ?? 7)->toDateString()
                        : null,
                    'follow_up_period' => $schedule?->follow_up_period,
                    'diagnosis' => $status === ClinicAppointment::STATUS_COMPLETED ? $diagnoses[$index % count($diagnoses)] : null,
                    'medications' => $status === ClinicAppointment::STATUS_COMPLETED ? $medications[$index % count($medications)] : null,
                    'rejection_reason' => $status === ClinicAppointment::STATUS_CANCELLED ? 'تم إلغاء الموعد بناء على طلب المريض.' : null,
                ]
            );

            $createdClinicAppointments->push($appointment);

            foreach ($examinationItems->slice(($index * 3) % max($examinationItems->count(), 1), 3) as $itemOffset => $item) {
                ExaminationRequest::updateOrCreate(
                    [
                        'clinic_appointment_id' => $appointment->id,
                        'examination_item_id' => $item->id,
                    ],
                    [
                        'lab_appointment_id' => null,
                        'status' => $status === ClinicAppointment::STATUS_COMPLETED && $itemOffset === 0
                            ? ExaminationRequest::STATUS_IN_PROGRESS
                            : ExaminationRequest::STATUS_PENDING,
                    ]
                );
            }
        }

        $requestBatches = ExaminationRequest::whereNull('lab_appointment_id')
            ->whereIn('clinic_appointment_id', $createdClinicAppointments->pluck('id'))
            ->get()
            ->groupBy('clinic_appointment_id')
            ->values();

        foreach ($requestBatches as $index => $requests) {
            if ($requests->isEmpty()) {
                continue;
            }

            $clinicAppointment = $createdClinicAppointments->firstWhere('id', $requests->first()->clinic_appointment_id);
            $lab = $labs[$index % $labs->count()];
            $status = $index % 3 === 0 ? LabAppointment::STATUS_COMPLETED : LabAppointment::STATUS_BOOKED;

            $labAppointment = LabAppointment::updateOrCreate(
                [
                    'lab_id' => $lab->id,
                    'patient_id' => $clinicAppointment->patient_id,
                    'created_at' => Carbon::parse($clinicAppointment->appointment_date)->addDay(),
                ],
                [
                    'status' => $status,
                    'result' => $status === LabAppointment::STATUS_COMPLETED
                        ? 'results/lab-appointments/result-' . ($index + 1) . '.pdf'
                        : null,
                ]
            );

            foreach ($requests as $request) {
                $request->update([
                    'lab_appointment_id' => $labAppointment->id,
                    'status' => $status === LabAppointment::STATUS_COMPLETED
                        ? ExaminationRequest::STATUS_COMPLETED
                        : ExaminationRequest::STATUS_IN_PROGRESS,
                ]);
            }
        }

        foreach ($patients->take(5)->values() as $index => $patient) {
            $lab = $labs[($index + 2) % $labs->count()];
            $status = $index % 2 === 0 ? LabAppointment::STATUS_BOOKED : LabAppointment::STATUS_COMPLETED;

            $labAppointment = LabAppointment::updateOrCreate(
                [
                    'lab_id' => $lab->id,
                    'patient_id' => $patient->id,
                    'created_at' => Carbon::today()->addDays($index + 4),
                ],
                [
                    'status' => $status,
                    'result' => $status === LabAppointment::STATUS_COMPLETED
                        ? 'results/lab-appointments/direct-result-' . ($index + 1) . '.pdf'
                        : null,
                ]
            );

            foreach ($examinationItems->slice(($index * 4) % max($examinationItems->count(), 1), 2) as $item) {
                ExaminationRequest::updateOrCreate(
                    [
                        'lab_appointment_id' => $labAppointment->id,
                        'examination_item_id' => $item->id,
                    ],
                    [
                        'clinic_appointment_id' => null,
                        'status' => $status === LabAppointment::STATUS_COMPLETED
                            ? ExaminationRequest::STATUS_COMPLETED
                            : ExaminationRequest::STATUS_IN_PROGRESS,
                    ]
                );
            }
        }
    }
}
