<?php

namespace App\Http\Controllers\Api\Clinics;

use App\Http\Controllers\Concerns\FormatsTime;
use App\Http\Controllers\Controller;
use App\Models\ClinicAppointment;
use App\Models\ClinicSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ClinicScheduleController extends Controller
{
    use FormatsTime;

    /**
     * GET /api/clinics/schedule
     * إرجاع إعدادات الحجز الحالية للعيادة التابعة للمستخدم الحالي
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'لا توجد عيادة مرتبطة بهذا المستخدم'
            ], 400);
        }

        $schedule = $clinic->schedule()->with('weekdays:id,name')->first();

        if (!$schedule) {
            return response()->json([
                'status' => 200,
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'id' => $schedule->id,
                'start_time' => $this->formatTime($schedule->start_time),
                'end_time' => $this->formatTime($schedule->end_time),
                'session_duration' => $schedule->session_duration,      // الحقل الجديد
                'follow_up_period' => $schedule->follow_up_period,
                'booking_fee' => $this->normalizeFee($schedule->booking_fee),
                'is_available' => (bool) $schedule->is_available,
                'weekdays' => $schedule->weekdays->map(function ($day) {
                    return [
                        'id' => $day->id,
                        'name' => $day->name,
                    ];
                })->values(),
            ]
        ], 200);
    }

    /**
     * POST /api/clinics/schedule
     * إنشاء أو تحديث إعدادات الحجز للعيادة
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'لا توجد عيادة مرتبطة بهذا المستخدم'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'session_duration' => ['required', 'integer', 'min:1'],          // تم التعديل
            'follow_up_period' => ['required', 'integer', 'min:1'],
            'booking_fee' => ['required', 'numeric', 'min:0'],
            'is_available' => ['required', 'boolean'],
            'weekday_ids' => ['required', 'array', 'min:1'],
            'weekday_ids.*' => ['integer', 'exists:weekdays,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();

        try {
            $schedule = ClinicSchedule::updateOrCreate(
                ['clinic_id' => $clinic->id],
                [
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'session_duration' => $request->session_duration,      // تم التعديل
                    'follow_up_period' => $request->follow_up_period,
                    'booking_fee' => $request->booking_fee,
                    'is_available' => $request->is_available,
                ]
            );

            // تحديث الأيام المرتبطة
            $schedule->weekdays()->sync($request->weekday_ids);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'تم حفظ إعدادات الحجز بنجاح'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'حدث خطأ أثناء حفظ إعدادات الحجز'
            ], 500);
        }
    }

    /**
 * Legacy follow-up available dates helper.
 * إرجاع التواريخ المتاحة لحجز مواعيد المتابعة من قبل العيادة (بدون قاعدة الساعتين)
 * مع إرجاع follow_up_period الخاص بالعيادة
 */
public function getFollowUpAvailableDates(Request $request): JsonResponse
{
    $user = $request->user();
    $clinic = $user->ownedClinic;

    if (!$clinic) {
        return response()->json(['status' => 400, 'message' => 'لا توجد عيادة مرتبطة بهذا المستخدم'], 400);
    }

    $schedule = $clinic->schedule()->with('weekdays')->first();
    if (!$schedule || !$schedule->is_available) {
        return response()->json(['status' => 200, 'data' => [], 'follow_up_period' => $schedule?->follow_up_period ?? 0]);
    }

    $workingWeekdayIds = $schedule->weekdays->pluck('id')->toArray();
    if (empty($workingWeekdayIds)) {
        return response()->json(['status' => 200, 'data' => [], 'follow_up_period' => $schedule->follow_up_period]);
    }

    // تحويل أيام العمل (نفس الخريطة السابقة لأنها نفس نظام الترقيم)
    $map = [1=>6,2=>0,3=>1,4=>2,5=>3,6=>4,7=>5];
    $carbonDays = array_values(array_intersect_key($map, array_flip($workingWeekdayIds)));
    if (empty($carbonDays)) {
        return response()->json(['status' => 200, 'data' => [], 'follow_up_period' => $schedule->follow_up_period]);
    }

    $now = Carbon::now();
    $startDate = $now->copy()->startOfDay();
    $endDate = $now->copy()->addDays(39)->startOfDay(); // 40 يوم شاملة اليوم

    $availableDates = [];
    $current = $startDate->copy();

    while ($current <= $endDate) {
        if (in_array($current->dayOfWeek, $carbonDays)) {
            // لا يتم استبعاد اليوم الحالي لأي سبب (لا حاجة للـ cutoff)
            $availableDates[] = $current->toDateString();
        }
        $current->addDay();
    }

    return response()->json([
        'status' => 200,
        'data' => $availableDates,
        'follow_up_period' => $schedule->follow_up_period,
    ]);
}

    public function getAppointmentFollowUp(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'لا توجد عيادة مرتبطة بهذا المستخدم'
            ], 400);
        }

        $appointment = $clinic->appointments()
            ->where('id', $appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        $followUpAppointment = $clinic->appointments()
            ->where('previous_appointment_id', $appointment->id)
            ->first();

        $schedule = $clinic->schedule()->with('weekdays')->first();
        $followUpPeriod = $followUpAppointment?->follow_up_period ?? $schedule?->follow_up_period ?? 0;
        $followUpDate = $followUpAppointment?->follow_up_date?->toDateString();

        if (!$schedule) {
            return response()->json([
                'status' => 200,
                'data' => [],
                'follow_up_period' => $followUpPeriod,
                'follow_up_date' => $followUpDate,
            ], 200);
        }

        $workingWeekdayIds = $schedule->weekdays->pluck('id')->toArray();
        if (empty($workingWeekdayIds)) {
            return response()->json([
                'status' => 200,
                'data' => [],
                'follow_up_period' => $followUpPeriod,
                'follow_up_date' => $followUpDate,
            ], 200);
        }

        $map = [1 => 6, 2 => 0, 3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5];
        $carbonDays = array_values(array_intersect_key($map, array_flip($workingWeekdayIds)));

        if (empty($carbonDays)) {
            return response()->json([
                'status' => 200,
                'data' => [],
                'follow_up_period' => $followUpPeriod,
                'follow_up_date' => $followUpDate,
            ], 200);
        }

        $now = Carbon::now();
        $current = $now->copy()->startOfDay();
        $endDate = $now->copy()->addDays(39)->startOfDay();
        $availableDates = [];

        while ($current <= $endDate) {
            if (in_array($current->dayOfWeek, $carbonDays)) {
                $availableDates[] = $current->toDateString();
            }

            $current->addDay();
        }

        return response()->json([
            'status' => 200,
            'data' => $availableDates,
            'follow_up_period' => $followUpPeriod,
            'follow_up_date' => $followUpDate,
        ], 200);
    }

    public function storeFollowUp(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'لا توجد عيادة مرتبطة بهذا المستخدم'
            ], 400);
        }

        $appointment = $clinic->appointments()
            ->where('id', $appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'follow_up_date' => ['required', 'date', 'date_format:Y-m-d'],
            'follow_up_period' => ['required', 'integer', 'min:1'],
        ], [
            'follow_up_date.required' => 'تاريخ المتابعة مطلوب',
            'follow_up_date.date_format' => 'تاريخ المتابعة غير صالح',
            'follow_up_period.required' => 'فترة المتابعة مطلوبة',
            'follow_up_period.integer' => 'فترة المتابعة يجب أن تكون رقما صحيحا',
            'follow_up_period.min' => 'فترة المتابعة يجب أن تكون أكبر من صفر',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        $followUpAppointment = $clinic->appointments()
            ->where('previous_appointment_id', $appointment->id)
            ->first();

        if ($followUpAppointment) {
            $followUpAppointment->update([
                'follow_up_date' => $request->follow_up_date,
                'follow_up_period' => $request->follow_up_period,
            ]);
        } else {
            $followUpAppointment = ClinicAppointment::create([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'previous_appointment_id' => $appointment->id,
                'status' => ClinicAppointment::STATUS_PENDING_BOOKING,
                'type' => ClinicAppointment::TYPE_FOLLOW_UP,
                'appointment_date' => null,
                'appointment_time' => null,
                'booking_fee' => null,
                'follow_up_date' => $request->follow_up_date,
                'follow_up_period' => $request->follow_up_period,
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'تم حفظ موعد المتابعة بنجاح',
            'data' => [
                'id' => $followUpAppointment->id,
                'follow_up_date' => $followUpAppointment->follow_up_date?->toDateString(),
                'follow_up_period' => $followUpAppointment->follow_up_period,
            ],
        ], 200);
    }

    private function normalizeFee($value)
    {
        if ($value === null) {
            return 0;
        }

        return strpos((string) $value, '.') !== false
            ? (float) $value
            : (int) $value;
    }
}
