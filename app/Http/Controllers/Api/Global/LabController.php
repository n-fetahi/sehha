<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Concerns\FormatsTime;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 use App\Models\Lab;
 use Carbon\Carbon;

class LabController extends Controller
{
    use FormatsTime;

    public function show($lab_id)
    {
        // تجهيز المختبر مع العلاقات
        $lab = Lab::with(['schedule.weekdays', 'examinationItems.examinationType'])
                ->find($lab_id);

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر المطلوب غير موجود',
            ]);
        }

        // أيام العمل
        $workingDays = [];
        if ($lab->schedule) {
            $workingDays = $lab->schedule->weekdays->pluck('id')->toArray();
        }

        // تجميع الفحوصات حسب النوع
        $examinations = $lab->examinationItems
            ->groupBy('examinationType.id')
            ->map(function ($items, $typeId) {
                $type = $items->first()->examinationType;
                return [
                    'type_name' => $type->name,
                    'items' => $items->map(function ($item) {
                        return [
                            'id'   => $item->id,
                            'name' => $item->name,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

        // بناء الاستجابة
        $data = [
            'id'               => $lab->id,
            'name'             => $lab->name,
            'phone'            => $lab->phone,
            'location'         => $lab->location,
            'profile_picture'  => $lab->profile_picture,
            'medical_director' => $lab->medical_director,
            'rating'           => 3.5,
            'is_available'     => $lab->schedule ? $lab->schedule->is_available : false,
            'start_time'       => $lab->schedule ?  $this->formatTime($lab->schedule->start_time) : null,
            'end_time'         => $lab->schedule ?  $this->formatTime($lab->schedule->end_time) : null,
            'working_days'     => $workingDays,
            'examinations'     => $examinations,
        ];

        return response()->json([
            'status' => 200,
            'data'    => $data,
        ]);
    }

    public function index()
    {
        $labs = Lab::with(['user', 'schedule'])
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'lab')
                    ->where('user_status', 'approved');
            })
            ->whereHas('schedule')
            ->get()
            ->map(function ($lab) {
                return [
                    'id'       => $lab->id,
                    'name'     => $lab->name,
                    'location' => $lab->location,
                    'rating'   => 3.5, // قيمة افتراضية لحين إضافة نظام تقييمات
                ];
            });

        return response()->json([
            'status' => 200,
            'data'    => $labs,
        ]);
    }



}
