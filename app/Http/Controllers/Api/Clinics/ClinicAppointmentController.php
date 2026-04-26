<?php

namespace App\Http\Controllers\Api\Clinics;

use App\Http\Controllers\Controller;
use App\Models\ClinicAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClinicAppointmentController extends Controller
{
    /**
     * GET /api/clinics/appointments
     * استعراض جميع حجوزات العيادة التي يملكها المستخدم المسجل دخوله.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $clinic = $user->ownedClinic;  // العلاقة في موديل User: ownedClinic

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'العيادة غير موجودة'
            ], 400);
        }

        $appointments = $clinic->appointments()
            ->with('patient.user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->user->name ?? null,
                    'type' => $appointment->type,
                    'status' => $appointment->status,
                ];
            })->values()
        ], 200);
    }

    /**
     * GET /api/clinics/appointments/{appointment_id}
     * عرض تفاصيل حجز معين يخص عيادة المالك المسجل دخوله.
     */
    public function show(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'العيادة غير موجودة'
            ], 400);
        }

        $appointment = $clinic->appointments()
            ->with([
                'patient.user:id,name',
                'wallet:id,name',
                'examinationRequests:id,clinic_appointment_id,examination_item_id',
                'examinationRequests.examinationItem:id,name',
                'nextAppointment:id,previous_appointment_id',
            ])
            ->where('id', $appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        // بناء قائمة الفحوصات المطلوبة
        $examinations = $appointment->examinationRequests->map(function ($examRequest) {
            return [
                'examination_item_id' => $examRequest->examination_item_id,
                'examination_item_name' => $examRequest->examinationItem->name ?? null,
            ];
        })->values();

        return response()->json([
            'status' => 200,
            'data' => [
                'id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'patient_name' => $appointment->patient->user->name ?? null,
                'appointment_date' => $appointment->appointment_date->toDateString(),
                'appointment_time' => is_string($appointment->appointment_time)
                    ? $appointment->appointment_time
                    : $appointment->appointment_time->format('H:i'),
                'booking_fee' => $appointment->booking_fee,
                'wallet_id' => $appointment->wallet_id,
                'wallet_name' => $appointment->wallet->name ?? null,
                'type' => $appointment->type,
                'status' => $appointment->status,
                'rejection_reason' => $appointment->rejection_reason,
                'diagnosis' => $appointment->diagnosis,
                'medications' => $appointment->medications,
                'examinations' => $examinations,
                'next_appointment_id' => $appointment->nextAppointment->id ?? null,
                'follow_up_date' => $appointment->follow_up_date?->toDateString(),
                'follow_up_period' => $appointment->follow_up_period,
            ]
        ], 200);
    }

        /**
     * PATCH /api/clinics/appointments/{appointment_id}/status
     * تحديث حالة الحجز (status) وسبب الرفض الاختياري.
     */
    public function updateStatus(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'العيادة غير موجودة'
            ], 400);
        }

        $appointment = $clinic->appointments()->where('id', $appointment_id)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        // التحقق من المدخلات
        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                'string',
                'in:' . implode(',', [
                    ClinicAppointment::STATUS_PENDING_BOOKING,
                    ClinicAppointment::STATUS_PENDING,
                    ClinicAppointment::STATUS_APPROVED,
                    ClinicAppointment::STATUS_REJECTED,
                    ClinicAppointment::STATUS_COMPLETED,
                    ClinicAppointment::STATUS_WAITING,
                    ClinicAppointment::STATUS_NO_SHOW,
                    ClinicAppointment::STATUS_CANCELLED,
                ])
            ],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'حالة الحجز مطلوبة',
            'status.in' => 'حالة الحجز غير صالحة',
            'rejection_reason.max' => 'سبب الرفض يجب ألا يتجاوز 500 حرف',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        // تحديث الحالة
        $updateData = ['status' => $request->status];

        // نُحدِّث سبب الرفض فقط إذا أُرسل في الطلب (حتى لو كان فارغاً)
        if ($request->has('rejection_reason')) {
            $updateData['rejection_reason'] = $request->rejection_reason;
        }

        $appointment->update($updateData);

        return response()->json([
            'status' => 200,
            'message' => 'تم تحديث حالة الحجز بنجاح',
        ], 200);
    }

    /**
     * PATCH /api/clinics/appointments/{appointment_id}/medical-info
     * تحديث التشخيص (diagnosis) والأدوية (medications) الخاصة بالحجز.
     */
    public function updateMedicalInfo(Request $request, int $appointment_id): JsonResponse
    {
        $user = $request->user();
        $clinic = $user->ownedClinic;

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'العيادة غير موجودة'
            ], 400);
        }

        $appointment = $clinic->appointments()->where('id', $appointment_id)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'medications' => ['nullable', 'string', 'max:1000'],
        ], [
            'diagnosis.max' => 'التشخيص يجب ألا يتجاوز 1000 حرف',
            'medications.max' => 'الأدوية يجب ألا تتجاوز 1000 حرف',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        // تحديث الحقول المرسلة فقط (إن لم تُرسَل نبقِي القيمة الحالية)
        $appointment->update($request->only(['diagnosis', 'medications']));

        return response()->json([
            'status' => 200,
            'message' => 'تم تحديث المعلومات الطبية بنجاح',
        ], 200);
    }
}
