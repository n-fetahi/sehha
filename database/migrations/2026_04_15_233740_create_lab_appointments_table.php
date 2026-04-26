<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_appointments', function (Blueprint $table) {
            $table->id(); // المعرف

            $table->foreignId('lab_id')
                  ->constrained('labs')
                  ->cascadeOnDelete();

                 // إذا حُذف حجز العيادة تبقى القيمة NULL

            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->cascadeOnDelete();

            $table->enum('status', [
                'booked',      // تم الحجز
                'completed'    // مكتمل
            ])->default('booked');

            $table->string('result')->nullable();      // مسار ملف PDF لنتيجة الفحوصات (يقبل الفراغ)

            $table->timestamps();

            // فهارس لتحسين الأداء
            $table->index('status');
            $table->index(['lab_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_appointments');
    }
};
