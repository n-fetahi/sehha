<?php

namespace App\Http\Controllers\Api\Clinics;

use App\Http\Controllers\Api\Clinics\Concerns\ResolvesAuthenticatedClinic;
use App\Http\Controllers\Controller;
use App\Models\ExaminationRequest;
use App\Models\ExaminationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicAppointmentExaminationController extends Controller
{
    use ResolvesAuthenticatedClinic;

    /**
     * GET /api/clinics/appointments/{appointment_id}/examinations
     * جلب أنواع الفحوصات مع عناصرها مع بيان المحدد منها مسبقاً للحجز.
     */
    public function index(Request $request, int $appointment_id): JsonResponse
    {
        $clinic = $this->resolveAuthenticatedClinic($request);

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'العيادة غير موجودة'
            ], 400);
        }

        // التحقق من أن الحجز يخص هذه العيادة
        $appointment = $clinic->appointments()->where('id', $appointment_id)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 400,
                'message' => 'الحجز غير موجود'
            ], 400);
        }

        // جلب معرفات عناصر الفحوصات المرتبطة حالياً بهذا الحجز
        $selectedItemIds = $appointment->examinationRequests()
            ->pluck('examination_item_id')
            ->toArray();

        // جلب جميع أنواع الفحوصات مع عناصرها
        $types = ExaminationType::with('items:id,name,examination_type_id')->get();

        $data = $types->map(function ($type) use ($selectedItemIds) {
            return [
                'type_name' => $type->name,
                'items' => $type->items->map(function ($item) use ($selectedItemIds) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'is_selected' => in_array($item->id, $selectedItemIds),
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'status' => 200,
            'data' => $data,
        ], 200);
    }

    /**
     * POST /api/clinics/appointments/{appointment_id}/examinations
     * تحديث قائمة الفحوصات المطلوبة للحجز (حذف القديم وإضافة الجديد).
     */
    public function store(Request $request, int $appointment_id): JsonResponse
    {
        $clinic = $this->resolveAuthenticatedClinic($request);

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

        $request->validate([
            'examination_item_ids' => ['required', 'array'],
            'examination_item_ids.*' => ['integer', 'exists:examination_items,id'],
        ], [
            'examination_item_ids.required' => 'يجب إرسال قائمة الفحوصات',
            'examination_item_ids.*.exists' => 'أحد عناصر الفحص غير موجود',
        ]);

        // حذف الفحوصات القديمة المرتبطة بالحجز
        $appointment->examinationRequests()->delete();

        // إضافة الفحوصات الجديدة
        foreach ($request->examination_item_ids as $itemId) {
            ExaminationRequest::create([
                'clinic_appointment_id' => $appointment->id,
                'lab_appointment_id' => null,
                'examination_item_id' => $itemId,
                'status' => ExaminationRequest::STATUS_PENDING,
            ]);
        }

        \App\Services\AppNotificationService::examinationsAdded($appointment);

        return response()->json([
            'status' => 200,
            'message' => 'تم إضافة الفحوصات المطلوبة بنجاح',
        ], 200);
    }
}
