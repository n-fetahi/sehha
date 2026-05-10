<?php

namespace App\Http\Controllers\Api\Labs;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class LabAppointmentController extends Controller
{
    /**
     * GET /api/labs/appointments
     * استعراض كافة حجوزات المرضى الخاصة بالمختبر المسجل دخوله
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $lab = $user->ownedLab;

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر غير موجود'
            ], 400);
        }

        $appointments = $lab->appointments()
            ->with('patient.user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient?->user?->name,
                    'status' => $appointment->status,
                ];
            })->values()
        ], 200);
    }

    /**
     * GET /api/labs/appointments/{appointment_id}
     * استعراض تفاصيل حجز معين من جهة المختبر
     */
    public function show(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $lab = $user->ownedLab;

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر غير موجود'
            ], 400);
        }

        $appointment = $lab->appointments()
            ->with([
                'patient.user:id,name',
                'examinationRequests:id,lab_appointment_id,clinic_appointment_id,examination_item_id,status',
                'examinationRequests.examinationItem:id,name',
                'examinationRequests.clinicAppointment:id,clinic_id',
                'examinationRequests.clinicAppointment.clinic:id,name',
            ])
            ->where('id', $appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        $clinicName = optional(
            $appointment->examinationRequests
                ->first(fn ($examRequest) => !is_null($examRequest->clinic_appointment_id))
                ?->clinicAppointment
                ?->clinic
        )->name;

        return response()->json([
            'status' => 200,
            'data' => [
                'id' => $appointment->id,
                'clinic_name' => $clinicName,
                'patient_name' => $appointment->patient?->user?->name,
                'status' => $appointment->status,
                'result' => $appointment->result ? Storage::url($appointment->result) : null,
                'examination_names' => $appointment->examinationRequests
                    ->map(fn ($examRequest) => $examRequest->examinationItem?->name)
                    ->filter()
                    ->values(),
            ]
        ], 200);
    }

       /**
     * POST /api/labs/appointments/{appointment_id}/result
     * رفع ملف نتيجة الفحص (PDF) لحجز مختبر معين.
     */
    public function uploadResult(Request $request, int $appointment_id): JsonResponse
    {
        // 1. التحقق من أن المستخدم يملك مختبرًا
        $user = $request->user();
        $lab = $user->ownedLab;

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر غير موجود'
            ], 400);
        }

        // 2. جلب الحجز والتأكد من أنه يخص هذا المختبر
        $appointment = $lab->appointments()->where('id', $appointment_id)->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود أو لا يخص مختبرك'
            ], 400);
        }

        // 3. التحقق من صحة الملف المرفوع (PDF, حجم أقصى 10MB)
        $request->validate([
            'result' => [
                'required',
                File::types(['pdf'])
                    ->max(10 * 1024), // 10 ميجابايت
            ],
        ], [
            'result.required' => 'يجب إرفاق ملف النتيجة',
            'result.mimes'    => 'يجب أن يكون الملف من نوع PDF',
            'result.max'      => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ]);

        // 4. حفظ الملف في التخزين
        $file = $request->file('result');
        $folder = 'results';
        $filename = 'appointment_' . $appointment_id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename);

        if (!$path) {
            return response()->json([
                'status' => 500,
                'message' => 'فشل في رفع الملف، يرجى المحاولة مرة أخرى'
            ], 500);
        }

        // 5. تحديث الحجز: تخزين مسار النتيجة وتغيير الحالة إلى completed
        $appointment->update([
            'result' => $path,
            'status' => 'completed',
        ]);

        \App\Services\AppNotificationService::labResultReady($appointment);

        // 6. رد النجاح
        return response()->json([
            'status' => 200,
            'message' => 'تم رفع نتيجة الفحص بنجاح',
        ], 200);
    }
}
