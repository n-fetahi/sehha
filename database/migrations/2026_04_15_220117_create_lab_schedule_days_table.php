<?php
// database/migrations/xxxx_xx_xx_create_lab_schedule_days_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_schedule_days', function (Blueprint $table) {
            $table->id(); // المعرف

            $table->foreignId('lab_schedule_id')
                  ->constrained('lab_schedules')
                  ->cascadeOnDelete();

            $table->foreignId('weekday_id')
                  ->constrained('weekdays')
                  ->cascadeOnDelete();

            $table->timestamps(); // تاريخ الانشاء والتعديل

            // منع تكرار نفس اليوم لنفس إعدادات المختبر
            $table->unique(['lab_schedule_id', 'weekday_id'], 'lab_schedule_weekday_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_schedule_days');
    }
};
