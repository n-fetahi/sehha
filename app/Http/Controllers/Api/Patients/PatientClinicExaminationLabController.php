<?php

namespace App\Http\Controllers\Api\Patients;

use App\Http\Controllers\Controller;
use App\Models\ExaminationRequest;
use App\Models\Lab;
use App\Models\LabAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PatientClinicExaminationLabController extends Controller
{
    /**
     * GET /api/patients/clinic-appointments/{appointment_id}/labs
     * إرجاع المختبرات التي تقدم جميع الفحوصات المطلوبة في حجز عيادة معين (للمريض الحالي).
     */
public function index(Request $request, int $appointment_id): JsonResponse
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

    $examinationItemIds = ExaminationRequest::where('clinic_appointment_id', $appointment->id)
        ->pluck('examination_item_id')
        ->unique();

    if ($examinationItemIds->isEmpty()) {
        return response()->json([
            'status' => 200,
            'data' => []
        ], 200);
    }

    $requiredCount = count($examinationItemIds);

    $labs = Lab::whereHas('examinationItems', function ($q) use ($examinationItemIds, $requiredCount) {
            $q->whereIn('examination_item_id', $examinationItemIds)
              ->groupBy('lab_id')
              ->havingRaw('COUNT(DISTINCT examination_item_id) = ?', [$requiredCount]);
        })
        ->select('id', 'name', 'location', 'rating')
        ->get();

    return response()->json([
        'status' => 200,
        'data' => $labs
    ], 200);
}


     public function store(Request $request, int $appointment_id): JsonResponse
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

        // التحقق من المدخلات
        $validator = Validator::make($request->all(), [
            'lab_id' => ['required', 'integer', 'exists:labs,id'],
        ], [
            'lab_id.required' => 'معرف المختبر مطلوب',
            'lab_id.exists' => 'المختبر المختار غير موجود',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            // 1. إنشاء حجز المختبر
            $labAppointment = LabAppointment::create([
                'lab_id' => $request->lab_id,
                'patient_id' => $patient->id,
                'status' => LabAppointment::STATUS_BOOKED,
            ]);

            // 2. ربط جميع طلبات الفحوصات الخاصة بحجز العيادة بحجز المختبر الجديد
            ExaminationRequest::where('clinic_appointment_id', $appointment->id)
                ->update(['lab_appointment_id' => $labAppointment->id]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'تم حجز المختبر بنجاح وربط الفحوصات',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 400,
                'message' => 'حدث خطأ أثناء حجز المختبر',
            ], 400);
        }
    }
}
