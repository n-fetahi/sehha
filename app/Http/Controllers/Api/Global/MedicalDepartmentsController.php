<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalDepartment;
use App\Models\Clinic;

class MedicalDepartmentsController extends Controller
{
    public function show()
    {
        $departments = MedicalDepartment::select('id','name')->get();

        return response()->json([
            'status' => 200,
            'data' => $departments
        ]);
    }


 public function getClinicsByDepartment($department_id)
{
    $clinics = Clinic::with(['user', 'schedule'])
        ->where('medical_department_id', $department_id)
        ->whereHas('user', function ($query) {
            $query->where('user_type', 'clinic')
                  ->where('user_status', 'approved');
        })
        ->whereHas('schedule')
        ->get()
        ->map(function ($clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'location' => $clinic->location,
                'user_name' => $clinic->user->name ?? null,
                'rating' => 3.5, // قيمة افتراضية أو يمكن حسابها من جدول تقييمات مستقبلاً
            ];
        });

    return response()->json([
        'status' => 200,
        'data' => $clinics
    ]);
}
}
