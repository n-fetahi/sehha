<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\Clinic;
use App\Models\Lab;
use Illuminate\Http\Request;
use App\Http\Requests\LabRegisterRequest;
use App\Http\Requests\PatientRegisterRequest;
use App\Http\Requests\StoreClinicRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // =========================================================
    // 🟢 أولاً: تسجيل المرضى
    // =========================================================
    public function registerPatient(PatientRegisterRequest $request)
    {
        // =========================
        // ✅ Validation
        // =========================
        $request->validate([
            'name' => 'required',
            'phone' =>  'required',
            'password' => 'required|min:8',
            'birth_date' => 'required|date',
            'gender' => 'nullable|in:male,female'
        ]);

        // =========================
        // ❌ تحقق رقم الهاتف موجود
        // =========================
        if (User::where('phone', $request->phone)->exists()) {
            return response()->json([
                'status' => 400,
                'message' => 'رقم الجوال مسجل بالفعل. يرجى استخدام رقم آخر أو تسجيل الدخول'
            ], 400);
        }

        // =========================
        // ✅ إنشاء المستخدم
        // =========================
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'patient',
            'gender' => $request->gender ?? null,
            'user_status' => 'approved'
        ]);

        // =========================
        // ✅ إنشاء بيانات المريض
        // =========================
        Patient::create([
            'user_id' => $user->id,
            'birth_date' => $request->birth_date,
        ]);

        // =========================
        // 🔐 إنشاء توكن
        // =========================
        $token = $user->createToken('auth_token')->plainTextToken;

        // =========================
        // ✅ الرد الناجح
        // =========================
        return response()->json([
            'status' => 200,
            'token' => $token,
            'name' => $user->name,
            'user_id' => $user->id,
            'user_type' => $user->user_type
        ]);
    }


    // =========================================================
    // 🟢 ثانياً: تسجيل المختبر
    // =========================================================
public function registerLab(LabRegisterRequest $request)
{
    // تحقق من رقم الهاتف موجود مسبقاً
    if (User::where('phone', $request->phone)->exists()) {
        return response()->json([
            'status' => 400,
            'message' => 'رقم الجوال مسجل بالفعل. يرجى استخدام رقم آخر أو تسجيل الدخول.'
        ]);
    }

    // تحقق من رقم الترخيص موجود مسبقاً
    if (Lab::where('license_number', $request->license_number)->exists()) {
        return response()->json([
            'status' => 400,
            'message' => 'رقم الترخيص الطبي مستخدم بالفعل. يرجى التحقق من صحة رقم الترخيص الطبي.'
        ]);
    }

    // إنشاء مستخدم جديد للمختبر
    $user = User::create([
        'name' => $request->name,
        'phone' => $request->phone,
        'password' => bcrypt($request->password),
        'user_type' => 'lab',
        'gender' => $request->gender ?? null,
    ]);

    // ✅ تخزين الملفات مباشرة بدلاً من Base64
    $licensePath = $request->file('license')->store('licenses', 'public');
    $commercialPath = $request->file('commercial_reg')->store('commercial_regs', 'public');

    $profilePicturePath = $request->hasFile('profile_picture')
        ? $request->file('profile_picture')->store('profiles', 'public')
        : null;

    // إنشاء سجل المختبر
    Lab::create([
        'name' => $request->lab_name,
        'phone' => $request->lab_phone,
        'medical_director' => $request->medical_director,
        'location' => $request->location,
        'license_number' => $request->license_number,
        'license' => $licensePath,
        'commercial_reg' => $commercialPath,
        'user_id' => $user->id,
        'profile_picture' => $profilePicturePath,
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'تم ارسال طلب الانضمام بنجاح وتجري عملية المراجعة من قبل الفريق المختص'
    ]);
}
    // =========================================================
    // 🟢 ثالثاً: تسجيل العيادة
    // =========================================================
    public function registerClinic(StoreClinicRequest $request)
{
    // إنشاء مستخدم جديد
    $user = User::create([
        'name' => $request->name,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'user_type' => 'clinic',
        'gender' => $request->gender ?? null,
    ]);

    // ✅ تخزين الملفات مباشرة بدلاً من Base64
    $licensePath = $request->file('license')->store('licenses', 'public');
    $commercialPath = $request->file('commercial_reg')->store('commercial_regs', 'public');

    $profilePicturePath = $request->hasFile('profile_picture')
        ? $request->file('profile_picture')->store('profiles', 'public')
        : null;

    // إنشاء سجل العيادة
    Clinic::create([
        'user_id' => $user->id,
        'name' => $request->clinic_name,
        'phone' => $request->clinic_phone,
        'location' => $request->location,
        'years_of_experience' => $request->years_of_experience,
        'bio' => $request->bio,
        'medical_department_id' => $request->medical_department_id,
        'license_number' => $request->license_number,
        'license' => $licensePath,
        'commercial_reg' => $commercialPath,
        'profile_picture' => $profilePicturePath,
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'تم ارسال طلب الانضمام بنجاح وتجري عملية المراجعة من قبل الفريق المختص'
    ]);
}




    // =========================================================
    // 🟢خامساً: تسجيل الدخول
    // =========================================================
    public function login(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'phone' => 'required',
            'password' => 'required'
        ]);

        // البحث عن المستخدم
        $user = User::where('phone', $request->phone)->first();

        // ❌ رقم الهاتف خطأ
        if (!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'رقم الهاتف غير صحيح، يرجى التحقق من صحة ادخال رقم الهاتف'
            ], 400);
        }

        // ❌ كلمة المرور خطأ
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 400,
                'message' => 'كلمة المرور غير صحيحة، يرجى التحقق من صحة ادخال كلمة المرور'
            ], 400);
        }

        // =========================
        // ✅ إذا مفعل → خروج مباشر
        // =========================
        if ($user->user_status == 'approved') {

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 200,
                'token' => $token,
                'name' => $user->name,
                'user_id' => $user->id,
                'user_type' => $user->user_type,
            ]);
        }

          if ($user->user_status == 'pending') {
          return response()->json([
                    'status' => 400,
                    'message' => 'عذراً الحساب قيد المراجعة، سيتم اشعارك فور الانتهاء من ذلك .. يرجى الانتظار'
                ], 400);
        }

        if ($user->user_status == 'restricted') {
            return response()->json([
                'status' => 400,
                'message' => 'عذراً تم تقييد الحساب .. يرجى التواصل مع خدمة العملاء لمعرفة بقية التفاصيل'
            ], 400);
        }




    }



public function logout(Request $request)
{
    try {
        // محاولة حذف التوكن الحالي
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    } catch (\Exception $e) {
        // فشلت العملية لأي سبب
        return response()->json([
            'status' => 400,
            'message' => 'فشل تسجيل الخروج، يرجى المحاولة مرة أخرى'
        ], 400);
    }
}

}
