<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProviderStatusController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'user_id.required' => 'معرف المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::with(['ownedClinic', 'ownedLab'])->find($request->user_id);

        if (!in_array($user->user_type, ['clinic', 'lab'], true)) {
            return response()->json([
                'status' => 400,
                'message' => 'نوع المستخدم غير صالح لهذا الطلب',
            ], 400);
        }

        $provider = $user->user_type === 'clinic'
            ? $user->ownedClinic
            : $user->ownedLab;

        if (!$provider) {
            return response()->json([
                'status' => 400,
                'message' => 'بيانات مقدم الخدمة غير موجودة',
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'user_status' => $user->user_status,
                'license_status' => $provider->license_status,
                'commercial_reg_status' => $provider->commercial_reg_status,
                'rejection_reason' => $provider->rejection_reason,
            ],
        ], 200);
    }

    public function resubmitDocuments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'license' => ['nullable', 'file', 'max:10240', 'required_without:commercial_reg'],
            'commercial_reg' => ['nullable', 'file', 'max:10240', 'required_without:license'],
        ], [
            'user_id.required' => 'معرف المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
            'license.required_without' => 'يجب إرسال الترخيص أو السجل التجاري على الأقل',
            'commercial_reg.required_without' => 'يجب إرسال الترخيص أو السجل التجاري على الأقل',
            'license.file' => 'يجب أن يكون الترخيص ملف صحيح',
            'commercial_reg.file' => 'يجب أن يكون السجل التجاري ملف صحيح',
            'license.max' => 'حجم ملف الترخيص يجب ألا يتجاوز 10 ميجابايت',
            'commercial_reg.max' => 'حجم ملف السجل التجاري يجب ألا يتجاوز 10 ميجابايت',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'البيانات المرسلة غير صالحة',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::with(['ownedClinic', 'ownedLab'])->find($request->user_id);

        if (!in_array($user->user_type, ['clinic', 'lab'], true)) {
            return response()->json([
                'status' => 400,
                'message' => 'نوع المستخدم غير صالح لهذا الطلب',
            ], 400);
        }

        $provider = $user->user_type === 'clinic'
            ? $user->ownedClinic
            : $user->ownedLab;

        if (!$provider) {
            return response()->json([
                'status' => 400,
                'message' => 'بيانات مقدم الخدمة غير موجودة',
            ], 400);
        }

        if ($request->filled('license_number')) {
            $licenseNumberExists = $provider->newQuery()
                ->where('license_number', $request->license_number)
                ->whereKeyNot($provider->getKey())
                ->exists();

            if ($licenseNumberExists) {
                return response()->json([
                    'status' => 400,
                    'message' => 'رقم الترخيص الطبي مستخدم بالفعل',
                ], 400);
            }
        }

        $updateData = [];

        if ($request->filled('license_number')) {
            $updateData['license_number'] = $request->license_number;
        }

        if ($request->hasFile('license')) {
            if ($provider->license) {
                Storage::delete($provider->license);
            }

            $updateData['license'] = $request->file('license')->store('licenses');
            $updateData['license_status'] = 'pending';
        }

        if ($request->hasFile('commercial_reg')) {
            if ($provider->commercial_reg) {
                Storage::delete($provider->commercial_reg);
            }

            $updateData['commercial_reg'] = $request->file('commercial_reg')->store('commercial_regs');
            $updateData['commercial_reg_status'] = 'pending';
        }

        $provider->update($updateData);
        $user->update(['user_status' => 'pending']);

        return response()->json([
            'status' => 200,
            'message' => 'تم إرسال الوثائق للمراجعة بنجاح',
        ], 200);
    }
}
