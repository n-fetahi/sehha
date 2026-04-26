<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'phone' => [
                'required',
                'string',
                'unique:users,phone',
                'regex:/^(77|78|71|70|73)\d{7}$/'
            ],
            'password' => 'required|string|min:8',
            'clinic_name' => 'required|string|max:100',
            'clinic_phone' => 'required|string|max:20',
            'location' => 'required|string|max:255',
            'license_number' => 'required|string|unique:clinics,license_number',

            // ✅ تغيير من string إلى file
            'license' => 'required|file|max:10240', // 10MB
            'commercial_reg' => 'required|file|max:10240',
            'profile_picture' => 'nullable|file|max:5120', // 5MB

            'medical_department_id' => 'required|exists:medical_departments,id',
            'gender' => 'nullable|in:male,female',

            // اختياري
            'years_of_experience' => 'nullable|integer|min:0',
            'bio' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'رقم الجوال مسجل بالفعل. يرجى استخدام رقم آخر أو تسجيل الدخول.',
            'phone.regex' => 'رقم الجوال يجب أن يكون 9 أرقام يمنية ويبدأ بـ 77, 78, 71, 70 أو 73.',
            'license_number.unique' => 'رقم الترخيص الطبي مستخدم بالفعل. يرجى التحقق من صحة رقم الترخيص.',
            'name.required' => 'يرجى إدخال اسم المستخدم.',
            'clinic_name.required' => 'يرجى إدخال اسم العيادة.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'gender.in' => 'القيمة المرسلة للجنس يجب أن تكون "ذكر" أو "أنثى".',
            'required' => 'حقل :attribute مطلوب.',

            // ✅ رسائل إضافية للملفات
            'license.file' => 'يجب أن يكون الترخيص ملف صحيح.',
            'commercial_reg.file' => 'يجب أن يكون السجل التجاري ملف صحيح.',
            'license.max' => 'حجم ملف الترخيص يجب ألا يتجاوز 10 ميجابايت.',
            'commercial_reg.max' => 'حجم ملف السجل التجاري يجب ألا يتجاوز 10 ميجابايت.',
            'profile_picture.max' => 'حجم الصورة الشخصية يجب ألا يتجاوز 5 ميجابايت.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 400));
    }
}
