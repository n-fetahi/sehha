<?php

namespace App\Http\Controllers\Api\Patients;
use App\Http\Controllers\Controller;
use App\Models\ExaminationRequest;
use App\Models\LabAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class LabAppointmentController extends Controller
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

        $appointments = $patient->labAppointments()
            ->with('lab:id,name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'name' => $appointment->lab?->name,
                    'status' => $appointment->status,
                ];
            })->values()
        ], 200);
    }


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

        $appointment = $patient->labAppointments()
            ->with([
                'lab:id,name',
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
                'lab_name' => $appointment->lab?->name,
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
     * POST /api/labs/appointments
     * إنشاء حجز جديد في مختبر محدد للمريض المسجل دخوله
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'status' => 400,
                'message' => 'المريض المطلوب غير موجود'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'lab_id' => ['required', 'integer', 'exists:labs,id'],
            'examination_item_ids' => ['required', 'array', 'min:1'],
            'examination_item_ids.*' => ['required', 'integer', 'distinct', 'exists:examination_items,id'],
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
            $labAppointment = LabAppointment::create([
                'lab_id' => $request->lab_id,
                'patient_id' => $patient->id,
                'status' => LabAppointment::STATUS_BOOKED,
            ]);

            foreach ($request->examination_item_ids as $examinationItemId) {
                ExaminationRequest::create([
                    'clinic_appointment_id' => null,
                    'lab_appointment_id' => $labAppointment->id,
                    'examination_item_id' => $examinationItemId,
                    'status' => ExaminationRequest::STATUS_PENDING,
                ]);
            }

            DB::commit();

            \App\Services\AppNotificationService::newLabBooking($labAppointment);

            return response()->json([
                'status' => 200,
                'message' => 'تم الحجز بنجاح'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'حدث خطأ أثناء تنفيذ الحجز',
            ], 500);
        }
    }
}
