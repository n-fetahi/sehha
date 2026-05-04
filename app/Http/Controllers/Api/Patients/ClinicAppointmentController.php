<?php

namespace App\Http\Controllers\Api\Patients;

use App\Http\Controllers\Controller;
use App\Models\ClinicAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;

class ClinicAppointmentController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'status' => 400,
                'message' => 'المريض المطلوب غير موجود'
            ], 400);
        }

        $appointments = $patient->clinicAppointments()
            ->with('clinic:id,name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'clinic_name' => $appointment->clinic?->name,
                    'type' => $appointment->type,      // سيُرجِع "consultation" أو "follow_up"
                    'status' => $appointment->status,  // سيُرجِع "pending" أو "approved" إلخ
                ];
            })->values(),
        ], 200);
    }


    /**
 * GET /api/patients/clinic-appointments/{appointment_id}
 * عرض تفاصيل حجز عيادة معين للمريض المسجل دخوله.
 */
public function show(Request $request, int $appointment_id): JsonResponse
{
    $user = $request->user();
    $patient = $user->patient;

    if (!$patient) {
        return response()->json([
            'status' => 400,
            'message' => 'المريض المطلوب غير موجود'
        ], 400);
    }

    // جلب الحجز مع العلاقات المطلوبة
    $appointment = $patient->clinicAppointments()
        ->with([
            'clinic:id,name',
            'wallet:id,name',
            'examinationRequests:id,clinic_appointment_id,lab_appointment_id,examination_item_id', // أضفنا lab_appointment_id
            'examinationRequests.examinationItem:id,name',
            'nextAppointment:id,previous_appointment_id'
        ])
        ->where('id', $appointment_id)
        ->first();

    if (!$appointment) {
        return response()->json([
            'status' => 400,
            'message' => 'الحجز غير موجود'
        ], 400);
    }

    // بناء مصفوفة الفحوصات المطلوبة
    $examinations = $appointment->examinationRequests
        ->pluck('examinationItem.name')
        ->filter()   // لاستبعاد القيم null (إذا كان examinationItem غير موجود)
        ->values();  // لإعادة ترتيب المفاتيح من 0

    $examinations = $examinations->isEmpty() ? null : $examinations;


    $labAppointmentId = $appointment->examinationRequests->first()?->lab_appointment_id;

    // تجهيز الاستجابة
    return response()->json([
        'status' => 200,
        'data' => [
            'id' => $appointment->id,
            'clinic_id' => $appointment->clinic_id,
            'clinic_name' => $appointment->clinic->name ?? null,
            'appointment_date' => $appointment->appointment_date?->toDateString(),
            'appointment_time' => $appointment->appointment_time
                ? (is_string($appointment->appointment_time)
                    ? $appointment->appointment_time
                    : $appointment->appointment_time->format('H:i'))
                : null,
            'booking_fee' => $appointment->booking_fee,
            'wallet_id' => $appointment->wallet_id,
            'wallet_name' => $appointment->wallet->name ?? null,
            'type' => $appointment->type,
            'status' => $appointment->status,
            'rejection_reason' => $appointment->rejection_reason,
            'cancelled_reason' => $appointment->cancelled_reason,
            'diagnosis' => $appointment->diagnosis,
            'medications' => $appointment->medications,
            'lab_appointment_id' => $labAppointmentId,
            'examinations' => $examinations,
            'next_appointment_id' => $appointment->nextAppointment->id ?? null,
            'follow_up_date' => $appointment->follow_up_date?->toDateString(),
            'follow_up_period' => $appointment->follow_up_period,
        ]
    ], 200);
}

    /**
     * POST /api/patients/clinic-appointments
     * حجز موعد في عيادة للمريض المسجل دخوله.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. التحقق من وجود المريض
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'status' => 400,
                'message' => 'المريض المطلوب غير موجود'
            ], 400);
        }

        // 2. التحقق من صحة المدخلات
        $validator = Validator::make($request->all(), [
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
            'appointment_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'booking_fee' => ['required', 'numeric', 'min:0'],
            'wallet_id' => ['required', 'integer', 'exists:wallets,id'],
        ], [
            'clinic_id.required' => 'معرف العيادة مطلوب',
            'clinic_id.exists' => 'العيادة غير موجودة',
            'appointment_date.required' => 'تاريخ الموعد مطلوب',
            'appointment_date.date_format' => 'تاريخ الموعد غير صالح',
            'appointment_time.required' => 'وقت الموعد مطلوب',
            'appointment_time.date_format' => 'وقت الموعد غير صالح',
            'booking_fee.required' => 'مبلغ الحجز مطلوب',
            'booking_fee.numeric' => 'مبلغ الحجز يجب أن يكون رقمًا',
            'wallet_id.required' => 'معرف المحفظة مطلوب',
            'wallet_id.exists' => 'المحفظة غير موجودة',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        // 3. إنشاء الحجز
        $appointment = ClinicAppointment::create([
            'clinic_id' => $request->clinic_id,
            'patient_id' => $patient->id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'booking_fee' => $request->booking_fee,
            'wallet_id' => $request->wallet_id,
            'status' => ClinicAppointment::STATUS_PENDING,
            'type' => ClinicAppointment::TYPE_CONSULTATION,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'تم حجز الموعد بنجاح',
        ], 200);
    }



public function getAvailableDates(Request $request, $clinic_id): JsonResponse
{
    $clinic = Clinic::with('schedule.weekdays')->find($clinic_id);
    if (!$clinic) {
        return response()->json(['status' => 400, 'message' => 'العيادة غير موجودة'], 400);
    }
    $schedule = $clinic->schedule;
    if (!$schedule || !$schedule->is_available) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    $workingWeekdayIds = $schedule->weekdays->pluck('id')->toArray();
    if (empty($workingWeekdayIds)) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    // تحويل أيام العمل
    $map = [1=>6,2=>0,3=>1,4=>2,5=>3,6=>4,7=>5];
    $carbonDays = array_values(array_intersect_key($map, array_flip($workingWeekdayIds)));
    if (empty($carbonDays)) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    $now = Carbon::now(); // الوقت الحالي
    $todayDate = $now->toDateString();
    $endDate = $now->copy()->addDays(39)->startOfDay(); // 40 يوم شاملة اليوم

    // حساب وقت القطع (end_time - ساعتين) مع ربطه بتاريخ اليوم
    $endTime = Carbon::parse($schedule->end_time);
    $cutoffDateTime = Carbon::parse($todayDate . ' ' . $endTime->format('H:i'))->subHours(2);
    $cutoffTimeString = $cutoffDateTime->format('H:i'); // للمقارنة النصية الاحتياطية

    $availableDates = [];

    for ($i = 0; $i <= 39; $i++) {
        $date = $now->copy()->addDays($i)->startOfDay();
        $dateString = $date->toDateString();

        // شرط أن يكون اليوم من أيام العمل
        if (!in_array($date->dayOfWeek, $carbonDays)) {
            continue;
        }

        // إذا كان التاريخ هو اليوم الحالي
        if ($dateString === $todayDate) {
            // نحصل على الوقت الحالي بالساعة والدقيقة فقط
            $currentTime = $now->format('H:i');
            // نقارن: إذا كان الوقت الحالي < وقت القطع، نضيف اليوم، وإلا لا نضيف
            if ($currentTime < $cutoffTimeString) {
                $availableDates[] = $dateString;
            }
            // وإلا ن跳过 هذا اليوم
        } else {
            // الأيام الأخرى تضاف دائماً (ما دامت ضمن أيام العمل)
            $availableDates[] = $dateString;
        }
    }

    return response()->json([
        'status' => 200,
        'data' => $availableDates
    ]);
}


public function getFollowUpAvailableDates(Request $request, int $appointment_id): JsonResponse
{
    $user = $request->user();
    $patient = $user->patient;

    if (!$patient) {
        return response()->json([
            'status' => 400,
            'message' => 'Patient not found'
        ], 400);
    }

    $appointment = $patient->clinicAppointments()
        ->with('clinic.schedule.weekdays')
        ->where('id', $appointment_id)
        ->first();

    if (!$appointment) {
        return response()->json([
            'status' => 400,
            'message' => 'Appointment not found or does not belong to this patient'
        ], 400);
    }

    if (!$appointment->follow_up_date || !$appointment->follow_up_period) {
        return response()->json([
            'status' => 400,
            'message' => 'Follow-up date or period is not set for this appointment'
        ], 400);
    }

    $schedule = $appointment->clinic?->schedule;
    if (!$schedule || !$schedule->is_available) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    $workingWeekdayIds = $schedule->weekdays->pluck('id')->toArray();
    if (empty($workingWeekdayIds)) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    $map = [1 => 6, 2 => 0, 3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5];
    $carbonDays = array_values(array_intersect_key($map, array_flip($workingWeekdayIds)));
    if (empty($carbonDays)) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    $startDate = $appointment->follow_up_date->copy()->startOfDay();
    $availableDates = [];

    for ($i = 0; $i < (int) $appointment->follow_up_period; $i++) {
        $date = $startDate->copy()->addDays($i);

        if (!in_array($date->dayOfWeek, $carbonDays, true)) {
            continue;
        }

        $availableDates[] = $date->toDateString();
    }

    return response()->json([
        'status' => 200,
        'data' => $availableDates
    ]);
}


/**
 * GET /api/clinics/{clinic_id}/available-times?date=YYYY-MM-DD
 */

public function getAvailableTimes(Request $request, $clinic_id): JsonResponse
{
    // 1. التحقق من وجود معلمة date وتنسيقها يدوياً
    $dateString = $request->input('date');
    if (!$dateString) {
        return response()->json([
            'status' => 400,
            'message' => 'يرجى إرسال date بالتنسيق Y-m-d'
        ], 400);
    }

    // محاولة تحويل التاريخ إلى كائن Carbon
    try {
        $date = Carbon::createFromFormat('Y-m-d', $dateString);
        if (!$date || $date->format('Y-m-d') !== $dateString) {
            throw new \Exception('Invalid date');
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 400,
            'message' => 'التاريخ غير صالح: يجب أن يكون بصيغة Y-m-d'
        ], 400);
    }

    // 2. جلب العيادة وعلاقتها
    $clinic = Clinic::with('schedule')->find($clinic_id);
    if (!$clinic) {
        return response()->json(['status' => 400, 'message' => 'العيادة غير موجودة'], 400);
    }

    $schedule = $clinic->schedule;
    if (!$schedule || !$schedule->is_available) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    // 3. توليد الأوقات النظرية
    $start = Carbon::parse($schedule->start_time);
    $end = Carbon::parse($schedule->end_time);
    $duration = (int) $schedule->session_duration;

    $availableTimes = [];
    $current = clone $start;

    while ($current->copy()->addMinutes($duration)->lte($end)) {
        $timeString = $current->format('H:i');
        $availableTimes[$timeString] = $timeString;
        $current->addMinutes($duration);
    }

    if (empty($availableTimes)) {
        return response()->json(['status' => 200, 'data' => []]);
    }

    // 4. إذا كان التاريخ اليوم، نحذف الأوقات التي مضت
    if ($date->isToday()) {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        foreach ($availableTimes as $key => $time) {
            if ($time < $currentTime) {
                unset($availableTimes[$key]);
            }
        }
    }

    // 5. استبعاد الأوقات المحجوزة من جدول clinic_appointments
    // تأكد من استبدال ClinicAppointment باسم الموديل الصحيح لديك
    $bookedTimes = DB::table('clinic_appointments')
        ->where('clinic_id', $clinic_id)
        ->whereDate('appointment_date', $date->toDateString())
        ->whereIn('status', ['pending', 'approved', 'completed'])
        ->pluck('appointment_time')
        ->map(fn($t) => Carbon::parse($t)->format('H:i'))
        ->toArray();

    // إزالة المحجوزات من القائمة
    $availableTimes = array_values(array_diff($availableTimes, $bookedTimes));

    return response()->json([
        'status' => 200,
        'data' => $availableTimes,
    ]);
}


    /**
     * PATCH /api/patients/clinic-appointments/{appointment_id}/update
     * إلغاء حجز عيادة من قبل المريض (تحديث الحالة إلى cancelled).
     */
    public function update(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'status' => 400,
                'message' => 'Ø§Ù„Ù…Ø±ÙŠØ¶ ØºÙŠØ± Ù…ÙˆØ¬ÙˆØ¯'
            ], 400);
        }

        $appointment = $patient->clinicAppointments()
            ->where('id', $appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'Ø§Ù„Ø­Ø¬Ø² ØºÙŠØ± Ù…ÙˆØ¬ÙˆØ¯ Ø£Ùˆ Ù„Ø§ ÙŠØ®Øµ Ù‡Ø°Ø§ Ø§Ù„Ù…Ø±ÙŠØ¶'
            ], 400);
        }

        if (in_array($appointment->status, [
            ClinicAppointment::STATUS_CANCELLED,
            ClinicAppointment::STATUS_COMPLETED,
            ClinicAppointment::STATUS_NO_SHOW,
            ClinicAppointment::STATUS_REJECTED,
        ], true)) {
            return response()->json([
                'status' => 400,
                'message' => 'Ù„Ø§ ÙŠÙ…ÙƒÙ† ØªØ¹Ø¯ÙŠÙ„ Ù‡Ø°Ø§ Ø§Ù„Ø­Ø¬Ø² Ø¨Ø­Ø§Ù„ØªÙ‡ Ø§Ù„Ø­Ø§Ù„ÙŠØ©'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'appointment_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ù…Ø±Ø³Ù„Ø© ØºÙŠØ± ØµØ§Ù„Ø­Ø©',
                'errors' => $validator->errors(),
            ], 400);
        }

        $date = Carbon::createFromFormat('Y-m-d', $request->appointment_date)->startOfDay();
        $time = $request->appointment_time;
        $clinic = Clinic::with('schedule.weekdays')->find($appointment->clinic_id);
        $schedule = $clinic?->schedule;

        if (!$schedule || !$schedule->is_available) {
            return response()->json([
                'status' => 400,
                'message' => 'Ø§Ù„Ø¹ÙŠØ§Ø¯Ø© ØºÙŠØ± Ù…ØªØ§Ø­Ø© Ù„Ù„Ø­Ø¬Ø²'
            ], 400);
        }

        $workingWeekdayIds = $schedule->weekdays->pluck('id')->toArray();
        $map = [1 => 6, 2 => 0, 3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5];
        $carbonDays = array_values(array_intersect_key($map, array_flip($workingWeekdayIds)));

        if (!in_array($date->dayOfWeek, $carbonDays, true)) {
            return response()->json([
                'status' => 400,
                'message' => 'Ø§Ù„ØªØ§Ø±ÙŠØ® Ø§Ù„Ù…Ø­Ø¯Ø¯ Ù„ÙŠØ³ Ù…Ù† Ø£ÙŠØ§Ù… Ø¹Ù…Ù„ Ø§Ù„Ø¹ÙŠØ§Ø¯Ø©'
            ], 400);
        }

        if ($date->isToday() && $time < Carbon::now()->format('H:i')) {
            return response()->json([
                'status' => 400,
                'message' => 'ÙˆÙ‚Øª Ø§Ù„Ù…ÙˆØ¹Ø¯ ÙŠØ¬Ø¨ Ø£Ù† ÙŠÙƒÙˆÙ† ÙÙŠ Ø§Ù„Ù…Ø³ØªÙ‚Ø¨Ù„'
            ], 400);
        }

        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        $duration = (int) $schedule->session_duration;
        $availableTimes = [];
        $current = $start->copy();

        while ($duration > 0 && $current->copy()->addMinutes($duration)->lte($end)) {
            $availableTimes[] = $current->format('H:i');
            $current->addMinutes($duration);
        }

        if (!in_array($time, $availableTimes, true)) {
            return response()->json([
                'status' => 400,
                'message' => 'ÙˆÙ‚Øª Ø§Ù„Ù…ÙˆØ¹Ø¯ ØºÙŠØ± Ù…ØªØ§Ø­ ÙÙŠ Ø¬Ø¯ÙˆÙ„ Ø§Ù„Ø¹ÙŠØ§Ø¯Ø©'
            ], 400);
        }

        $isBooked = ClinicAppointment::query()
            ->where('clinic_id', $appointment->clinic_id)
            ->where('id', '!=', $appointment->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->whereTime('appointment_time', $time)
            ->whereIn('status', [
                ClinicAppointment::STATUS_PENDING,
                ClinicAppointment::STATUS_APPROVED,
                ClinicAppointment::STATUS_COMPLETED,
                ClinicAppointment::STATUS_WAITING,
            ])
            ->exists();

        if ($isBooked) {
            return response()->json([
                'status' => 400,
                'message' => 'Ù‡Ø°Ø§ Ø§Ù„ÙˆÙ‚Øª Ù…Ø­Ø¬ÙˆØ² Ù…Ø³Ø¨Ù‚Ø§Ù‹'
            ], 400);
        }

        $appointment->update([
            'appointment_date' => $date->toDateString(),
            'appointment_time' => $time,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'ØªÙ… ØªØ¹Ø¯ÙŠÙ„ Ù…ÙˆØ¹Ø¯ Ø§Ù„Ø­Ø¬Ø² Ø¨Ù†Ø¬Ø§Ø­',
            'data' => [
                'id' => $appointment->id,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'appointment_time' => $appointment->appointment_time
                    ? (is_string($appointment->appointment_time)
                        ? $appointment->appointment_time
                        : $appointment->appointment_time->format('H:i'))
                    : null,
            ],
        ], 200);
    }

    public function cancel(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'status' => 400,
                'message' => 'المريض غير موجود'
            ], 400);
        }

        $appointment = $patient->clinicAppointments()
            ->where('id', $appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود أو لا يخص هذا المريض'
            ], 400);
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'تم إلغاء الحجز بنجاح',
        ], 200);
    }
}
