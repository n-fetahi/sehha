<?php

namespace App\Http\Controllers\Api\Clinics;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SecretaryController extends Controller
{
    /**
     * عرض بيانات سكرتير العيادة
     * GET /api/clinic/secretary
     */
    public function show()
    {
        $authUser = auth()->user();

        if ($authUser->user_type !== 'clinic') {
            return response()->json([
                'status'  => 400,
                'message' => 'غير مصرح بهذه العملية',
            ], 403);
        }

        $clinic = Clinic::where('user_id', $authUser->id)->first();
        if (!$clinic) {
            return response()->json([
                'status'  => 400,
                'message' => 'العيادة غير موجودة',
            ], 404);
        }

        if (!$clinic->secretary_id) {
            return response()->json([
                'status' => 200,
                'data'   => null,
            ], 200);
        }

        $secretary = User::find($clinic->secretary_id);
        if (!$secretary) {
            return response()->json([
                'status' => 200,
                'data'   => null,
            ], 200);
        }

        return response()->json([
            'status' => 200,
            'data'   => [
                'id'          => $secretary->id,
                'name'        => $secretary->name,
                'phone'       => $secretary->phone,
                'gender'      => $secretary->gender,
                'password'    => '********',
                'user_status' => $secretary->user_status,
            ],
        ], 200);
    }

    /**
     * إضافة سكرتير جديد أو تعديل بيانات سكرتير موجود
     * POST /api/clinic/secretary
     */
    public function storeOrUpdate(Request $request)
    {
        $authUser = auth()->user();

        if ($authUser->user_type !== 'clinic') {
            return response()->json([
                'status'  => 400,
                'message' => 'غير مصرح بهذه العملية',
            ], 403);
        }

        $clinic = Clinic::where('user_id', $authUser->id)->first();
        if (!$clinic) {
            return response()->json([
                'status'  => 400,
                'message' => 'العيادة غير موجودة',
            ], 404);
        }

        $isUpdate = !is_null($clinic->secretary_id);

        // قواعد التحقق
        $rules = [
            'name'   => 'required|string|max:255',
            'phone'  => [
                'required',
                'string',
                'regex:/^(77|78|73|70|71)\d{7}$/', // 9 أرقام يمنية تبدأ بالمفاتيح المحددة
                Rule::unique('users', 'phone')->ignore($isUpdate ? $clinic->secretary_id : null),
            ],
            'gender' => 'required|in:male,female',
        ];

        if (!$isUpdate) {
            $rules['password']    = 'required|string|min:6';
            $rules['user_status'] = 'required|in:approved,restricted';
        } else {
            $rules['password']    = 'nullable|string|min:6';
            $rules['user_status'] = 'nullable|in:approved,restricted';
        }

        // رسائل خطأ مخصصة للهاتف
        $messages = [
            'phone.regex' => 'يجب أن يتكون رقم الهاتف من 9 أرقام ويبدأ بـ 77, 78, 73, 70, 71 (بدون مفتاح الدولة).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // تنفيذ العملية
        if ($isUpdate) {
            $secretary = User::find($clinic->secretary_id);
            if (!$secretary) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'السكرتير غير موجود',
                ], 400);
            }

            $secretary->name   = $request->name;
            $secretary->phone  = $request->phone;
            $secretary->gender = $request->gender;

            if ($request->has('user_status')) {
                $secretary->user_status = $request->user_status;
            }

            if ($request->filled('password')) {
                $secretary->password = Hash::make($request->password);
            }

            $secretary->save();

            return response()->json([
                'status'  => 200,
                'message' => 'تمت عملية التعديل بنجاح',
            ], 200);

        } else {
            $secretary = User::create([
                'name'        => $request->name,
                'phone'       => $request->phone,
                'gender'      => $request->gender,
                'password'    => Hash::make($request->password),
                'user_type'   => 'secretary',
                'user_status' => $request->user_status,
            ]);

            $clinic->secretary_id = $secretary->id;
            $clinic->save();

            return response()->json([
                'status'  => 200,
                'message' => 'تمت عملية الاضافة بنجاح',
            ], 200);
        }
    }
}
