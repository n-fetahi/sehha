<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LabRegisterRequest extends FormRequest
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
            'lab_name' => 'required|string|max:100',
            'lab_phone' => 'required|string|max:20',
            'medical_director' => 'nullable|string|max:100',
            'location' => 'required|string|max:255',
            'license_number' => 'required|string|unique:labs,license_number',

            // ✅ التعديل هنا فقط - من string إلى file
            'license' => 'required|file|max:10240', // 10MB
            'commercial_reg' => 'required|file|max:10240',
            'profile_picture' => 'nullable|file|max:5120', // 5MB

            'gender' => 'nullable|in:male,female',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'رقم الجوال مسجل بالفعل. يرجى استخدام رقم آخر أو تسجيل الدخول.',
            'phone.regex' => 'رقم الجوال يجب أن يكون 9 أرقام يمنية ويبدأ بـ 77, 78, 71, 70 أو 73.',
            'license_number.unique' => 'رقم الترخيص الطبي مستخدم بالفعل. يرجى التحقق من صحة رقم الترخيص الطبي.',
            'name.required' => 'يرجى إدخال اسم المستخدم.',
            'lab_name.required' => 'يرجى إدخال اسم المختبر.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'gender.in' => 'القيمة المرسلة للجنس يجب أن تكون "ذكر" أو "أنثى".',
            'required' => 'حقل :attribute مطلوب.',

            // رسائل إضافية للملفات
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
