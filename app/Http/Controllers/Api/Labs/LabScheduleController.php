<?php

namespace App\Http\Controllers\Api\Labs;

use App\Http\Controllers\Concerns\FormatsTime;
use App\Http\Controllers\Controller;
use App\Models\LabSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LabScheduleController extends Controller
{
    use FormatsTime;
    /**
     * GET /api/labs/schedule
     * إرجاع إعدادات الحجز الحالية للمختبر التابع للمستخدم الحالي
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $lab = $user->ownedLab;

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر المطلوب غير موجود'
            ], 400);
        }

        $schedule = $lab->schedule()->with('weekdays:id,name')->first();

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
     * POST /api/labs/schedule
     * إنشاء أو تحديث إعدادات الحجز للمختبر
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $lab = $user->ownedLab;

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر المطلوب غير موجود'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
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
            $schedule = LabSchedule::updateOrCreate(
                ['lab_id' => $lab->id],
                [
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'is_available' => $request->is_available,
                ]
            );

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


}
