<?php
// database/migrations/xxxx_xx_xx_create_clinic_schedule_days_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_schedule_days', function (Blueprint $table) {
            $table->id(); // المعرف

            $table->foreignId('clinic_schedule_id')
                  ->constrained('clinic_schedules')
                  ->cascadeOnDelete();

            $table->foreignId('weekday_id')
                  ->constrained('weekdays')
                  ->cascadeOnDelete();

            $table->timestamps(); // تاريخ الانشاء والتعديل

            // منع تكرار نفس اليوم لنفس إعدادات العيادة
            $table->unique(['clinic_schedule_id', 'weekday_id'], 'clinic_schedule_weekday_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_schedule_days');
    }
};
