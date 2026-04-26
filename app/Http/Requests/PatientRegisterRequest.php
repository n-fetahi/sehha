<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {  
        return [
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'nullable|in:male,female',
      'phone' => ['required', 'digits:9','regex:/^(77|73|78|71)/','unique:users,phone'],
            'password' => 'required|string|min:8',
            
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال اسم المستخدم.',
            'birth_date.required' => 'يرجى إدخال تاريخ الميلاد.',
            'birth_date.date' => 'صيغة تاريخ الميلاد غير صحيحة.',
            'birth_date.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم.',
            'gender.in' => 'القيمة المرسلة للجنس يجب أن تكون "male" أو "female".',
            'phone.required' => 'يرجى إدخال رقم الجوال.',
            'phone.digits' => 'رقم الجوال يجب أن يتكون من 9 أرقام.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 77 أو 73 أو 78 أو 71.',
            'phone.unique' => 'رقم الجوال مسجل بالفعل. يرجى استخدام رقم آخر أو تسجيل الدخول.', 'password.required' => 'يرجى إدخال كلمة المرور.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'user_type.in' => 'نوع المستخدم غير صالح. يجب أن يكون patient أو clinic أو lab أو admin.',
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