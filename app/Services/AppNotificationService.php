<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\ClinicAppointment;
use App\Models\LabAppointment;

class AppNotificationService
{
    // =============================================
    //          حجوزات العيادات
    // =============================================

    public static function newClinicBooking(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('clinic', 'patient.user');

        $patientName = $appointment->patient?->user?->name ?? 'مريض';
        $date = $appointment->appointment_date?->toDateString() ?? 'غير محدد';
        $title = 'طلب حجز جديد';
        $content = "لديك طلب حجز جديد من {$patientName} ليوم {$date}";

        self::store($appointment->clinic->user_id, $title, $content);
        if ($appointment->clinic->secretary_id) {
            self::store($appointment->clinic->secretary_id, $title, $content);
        }
    }

    public static function clinicApproved(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $date = $appointment->appointment_date?->toDateString() ?? 'غير محدد';
        $title = 'تمت الموافقة';
        $content = "تمت الموافقة على حجزك يوم {$date}";

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function clinicRejected(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $reason = $appointment->rejection_reason ?? 'غير محدد';
        $title = 'تم رفض الحجز';
        $content = "تم رفض حجزك بسبب: {$reason}";

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function clinicCompleted(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $title = 'تم الكشف';
        $content = 'تم اكتمال الكشف، يمكنك الاطلاع على النتائج';

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function clinicCancelled(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('clinic', 'patient.user');

        $patientName = $appointment->patient?->user?->name ?? 'مريض';
        $date = $appointment->appointment_date?->toDateString() ?? 'غير محدد';
        $title = 'إلغاء حجز';
        $content = "ألغى {$patientName} حجزه ليوم {$date}";

        self::store($appointment->clinic->user_id, $title, $content);
        if ($appointment->clinic->secretary_id) {
            self::store($appointment->clinic->secretary_id, $title, $content);
        }
    }

    public static function examinationsAdded(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $title = 'فحوصات جديدة';
        $content = 'تم إضافة فحوصات لحجزك';

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function medicalInfoAdded(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $title = 'تم إضافة التشخيص';
        $content = 'تم إضافة التشخيص والأدوية لحجزك';

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function followUpAdded(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $followUpDate = $appointment->follow_up_date?->toDateString() ?? 'غير محدد';
        $title = 'موعد متابعة';
        $content = "تم إضافة موعد متابعة لك يوم {$followUpDate}";

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function appointmentWaiting(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $title = 'موعدك الآن';
        $content = 'حان موعدك، يرجى التوجه للعيادة';

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function appointmentNoShow(ClinicAppointment $appointment): void
    {
        $appointment->loadMissing('clinic', 'patient.user');

        $patientName = $appointment->patient?->user?->name ?? 'مريض';
        $date = $appointment->appointment_date?->toDateString() ?? 'غير محدد';
        $title = 'لم يحضر';
        $content = "لم يحضر {$patientName} للموعد في {$date}";

        self::store($appointment->clinic->user_id, $title, $content);
        if ($appointment->clinic->secretary_id) {
            self::store($appointment->clinic->secretary_id, $title, $content);
        }
    }

    // =============================================
    //          حجوزات المختبرات
    // =============================================

    public static function newLabBooking(LabAppointment $appointment): void
    {
        $appointment->loadMissing('lab', 'patient.user');

        $patientName = $appointment->patient?->user?->name ?? 'مريض';
        $title = 'طلب فحص جديد';
        $content = "لديك طلب فحص جديد من {$patientName}";

        self::store($appointment->lab->user_id, $title, $content);
    }

    public static function labResultReady(LabAppointment $appointment): void
    {
        $appointment->loadMissing('patient');

        $title = 'النتيجة جاهزة';
        $content = 'نتيجة الفحص جاهزة للمشاهدة';

        self::store($appointment->patient->user_id, $title, $content);
    }

    public static function labCancelled(LabAppointment $appointment): void
    {
        $appointment->loadMissing('lab', 'patient.user');

        $patientName = $appointment->patient?->user?->name ?? 'مريض';
        $title = 'إلغاء فحص';
        $content = "ألغى {$patientName} طلب الفحص";

        self::store($appointment->lab->user_id, $title, $content);
    }

    // =============================================
    //          داخلي
    // =============================================

    private static function store(int $userId, string $title, string $content): void
    {
        if (!$userId) {
            return;
        }

        AppNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'is_delivered' => false,
        ]);
    }
}
