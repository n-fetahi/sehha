<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Concerns\FormatsTime;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;



class ClinicController extends Controller
{
    use FormatsTime;

    public function show($clinic_id)
    {
        $clinic = Clinic::with(['user', 'schedule.weekdays'])
            ->where('id', $clinic_id)
            ->first();

        if (!$clinic) {
            return response()->json([
                'status' => 400,
                'message' => 'العيادة المطلوبة غير موجودة',
            ]);
        }

        // استخراج أيام العمل
        $workingDays = [];
        if ($clinic->schedule) {
            $workingDays = $clinic->schedule->weekdays->pluck('id')->toArray();
        }

        $data = [
            'id'                  => $clinic->id,
            'name'                => $clinic->name,
            'phone'               => $clinic->phone,
            'location'            => $clinic->location,
            'profile_picture'     => $clinic->profile_picture ? Storage::url($clinic->profile_picture) : null,
            'user_name'           => $clinic->user->name ?? null,
            'rating'              => 3.5,
            'years_of_experience' => $clinic->years_of_experience,
            'bio'                 => $clinic->bio,
            'is_available'        => $clinic->schedule ? $clinic->schedule->is_available : false,
            'booking_fee'         => $clinic->schedule ? $clinic->schedule->booking_fee : "0",
            'working_days'        => $workingDays,
            'start_time'          => $clinic->schedule ? $this->formatTime($clinic->schedule->start_time) : null,
            'end_time'            => $clinic->schedule ? $this->formatTime($clinic->schedule->end_time) : null,
        ];

        return response()->json([
            'status' => 200,
            'data'    => $data,
        ]);
    }

}
