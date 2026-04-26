<?php
// database/migrations/xxxx_xx_xx_create_clinic_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_schedules', function (Blueprint $table) {
            $table->id(); // المعرف

            $table->foreignId('clinic_id')
                  ->constrained('clinics')
                  ->cascadeOnDelete(); // إذا حُذفت العيادة تُحذف إعداداتها

            $table->time('start_time');        // وقت بداية الدوام
            $table->time('end_time');          // وقت نهاية الدوام

            $table->unsignedSmallInteger('consultation_duration'); // مدة الاستشارة (بالدقائق)
            $table->unsignedSmallInteger('follow_up_duration');    // مدة المتابعة/العودة للعيادة (بالدقائق)
            $table->unsignedSmallInteger('follow_up_period');      // فترة المتابعة (بالأيام)

            $table->decimal('booking_fee', 10, 2)->default(0.00); // مبلغ الحجز

            $table->boolean('is_available')->default(false); // هل هو متاح للحجوزات

            $table->timestamps(); // تاريخ الانشاء والتعديل

            // منع تكرار إعدادات لنفس العيادة (كل عيادة لها إعداد واحد فقط)
            $table->unique('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_schedules');
    }
};
