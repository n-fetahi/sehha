<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')
                  ->constrained('clinics')
                  ->cascadeOnDelete();

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->cascadeOnDelete();


            // ✅ مفتاح أجنبي للحجز السابق (self-referencing)
            $table->foreignId('previous_appointment_id')
                  ->nullable()
                  ->constrained('clinic_appointments')
                  ->nullOnDelete();


            // ✅ الإضافة الجديدة: مفتاح أجنبي لجدول المحافظ
            $table->foreignId('wallet_id')
                  ->nullable()
                  ->constrained('wallets')
                  ->nullOnDelete();

            // حالة الحجز
            $table->enum('status', [
                'pending_booking',   // انتظار الحجز
                'pending',           // معلق
                'approved',          // مقبول
                'rejected',          // مرفوض
                'completed',         // مكتمل
                'waiting',           // انتظار الحضور
                'no_show',           // لم يتم الحضور
                'cancelled'          // ملغي
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            // نوع الحجز
            $table->enum('type', [
                'consultation',      // استشارة
                'follow_up'          // متابعة/عودة
            ])->default('consultation');

            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->date('follow_up_date')->nullable();
            $table->unsignedSmallInteger('follow_up_period')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('medications')->nullable();
            $table->decimal('booking_fee', 10, 2)->default(0.00); // مبلغ الحجز
            $table->timestamps();

            // فهارس
            $table->index('appointment_date');
            $table->index('status');
            $table->index(['clinic_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_appointments');
    }
};
