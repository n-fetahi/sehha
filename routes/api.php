<?php

use App\Http\Controllers\Api\Clinics\ClinicScheduleController;
use App\Http\Controllers\Api\Global\AuthController;
use App\Http\Controllers\Api\Global\ClinicController;
use App\Http\Controllers\Api\Global\LabController;
use App\Http\Controllers\Api\Global\MedicalDepartmentsController;
use App\Http\Controllers\Api\Labs\LabScheduleController;
use App\Http\Controllers\Api\Patients\LabAppointmentController as PatientLabAppointmentController;
use App\Http\Controllers\Api\Labs\LabAppointmentController as LabOwnerAppointmentController;
use App\Http\Controllers\Api\Labs\LabExaminationController;
use App\Http\Controllers\Api\Patients\ClinicAppointmentController as PatientClinicAppointmentController;
use App\Http\Controllers\Api\Clinics\ClinicAppointmentController as ClinicOwnerAppointmentController;
use App\Http\Controllers\Api\Clinics\ClinicAppointmentExaminationController;
use App\Http\Controllers\Api\Clinics\SecretaryController;
use App\Http\Controllers\Api\Patients\PatientClinicExaminationLabController;
use App\Http\Controllers\Api\Patients\PatientWalletController;
use App\Http\Controllers\Api\Global\AppNotificationController;
use Illuminate\Support\Facades\Route;


Route::post('/patients/register', [AuthController::class, 'registerPatient']);

Route::post('/labs/register', [AuthController::class, 'registerLab']);

Route::post('/clinics/register', [AuthController::class, 'registerClinic']);

Route::post('/verify-code', [AuthController::class, 'verifyRegistrationCode']);

Route::post('/resend-verify-code', [AuthController::class, 'resendVerificationCode']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/medical-departments', [MedicalDepartmentsController::class, 'show']);

Route::get('/medical-departments/{department_id}/clinics',
    [MedicalDepartmentsController::class, 'getClinicsByDepartment']
);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->prefix('clinics')->group(function () {

    Route::get('/schedule', [ClinicScheduleController::class, 'show']);
    Route::post('/schedule', [ClinicScheduleController::class, 'store']);
    Route::get('/follow-up/available-dates', [ClinicScheduleController::class, 'getFollowUpAvailableDates']);
    Route::get('appointments', [ClinicOwnerAppointmentController::class, 'index']);
    Route::get('appointments/{appointment_id}', [ClinicOwnerAppointmentController::class, 'show']);
    Route::patch('appointments/{appointment_id}/status', [ClinicOwnerAppointmentController::class, 'updateStatus']);
    Route::patch('appointments/{appointment_id}/medical-info', [ClinicOwnerAppointmentController::class, 'updateMedicalInfo']);
        Route::get('appointments/{appointment_id}/examinations', [ClinicAppointmentExaminationController::class, 'index']);
    Route::post('appointments/{appointment_id}/examinations', [ClinicAppointmentExaminationController::class, 'store']);
    Route::post('/secretary', [SecretaryController::class, 'storeOrUpdate']);
    Route::get('/secretary', [SecretaryController::class, 'show']);


});


Route::middleware('auth:sanctum')->prefix('patients')->group(function () {

    Route::get('lab-appointments', [PatientLabAppointmentController::class, 'index']);
    Route::get('/lab-appointments/{appointment_id}', [PatientLabAppointmentController::class, 'show']);
    Route::post('/lab-appointments', [PatientLabAppointmentController::class, 'store']);

    Route::get('/wallets', [PatientWalletController::class, 'index']);

    Route::get('/{clinic_id}/available-dates', [PatientClinicAppointmentController::class, 'getAvailableDates']);
    Route::get('/{clinic_id}/available-times', [PatientClinicAppointmentController::class, 'getAvailableTimes']);
    Route::post('clinic-appointments', [PatientClinicAppointmentController::class, 'store']);
    Route::get('clinic-appointments', [PatientClinicAppointmentController::class, 'index']);
    Route::get('clinic-appointments/{appointment_id}', [PatientClinicAppointmentController::class, 'show']);
    Route::patch('clinic-appointments/{appointment_id}', [PatientClinicAppointmentController::class, 'cancel']);
    Route::get('clinic-appointments/{appointment_id}/labs', [PatientClinicExaminationLabController::class, 'index']);
    Route::post('clinic-appointments/{appointment_id}/labs', [PatientClinicExaminationLabController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('labs')->group(function () {
    Route::get('/schedule', [LabScheduleController::class, 'show']);
    Route::post('/schedule', [LabScheduleController::class, 'store']);
      Route::get('appointments', [LabOwnerAppointmentController::class, 'index']);
    Route::get('appointments/{appointment_id}', [LabOwnerAppointmentController::class, 'show']);
    Route::post('appointments/{appointment_id}/result', [LabOwnerAppointmentController::class, 'uploadResult']);
    Route::get('/examination-types', [LabExaminationController::class, 'getExaminationTypes']);
    Route::get('/examination-types/{examination_type_id}', [LabExaminationController::class, 'getExaminationsByType']);
    Route::post('/examination-items', [LabExaminationController::class, 'syncExaminationItems']);

});

Route::get('/labs',[LabController::class, 'index']);
Route::get('/labs/{lab_id}',[LabController::class, 'show']);
Route::get('/clinics/{clinic_id}',[ClinicController::class, 'show']);

// =========================================================
// 🔔 الإشعارات
// =========================================================
Route::get('/notifications', [AppNotificationController::class, 'index']);
Route::patch('/notifications/{notification_id}/delivered', [AppNotificationController::class, 'markDelivered']);




