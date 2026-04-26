<?php
// database/migrations/xxxx_xx_xx_create_lab_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_schedules', function (Blueprint $table) {
            $table->id(); // المعرف

            $table->foreignId('lab_id')
                  ->constrained('labs')
                  ->cascadeOnDelete(); // إذا حُذف المختبر تُحذف إعداداته

            $table->time('start_time'); // وقت بداية الدوام (مثال: 08:00:00)
            $table->time('end_time');   // وقت نهاية الدوام (مثال: 16:00:00)

            $table->boolean('is_available')->default(false); // هل هو متاح للحجوزات

            $table->timestamps(); // تاريخ الانشاء والتعديل

            // منع تكرار إعدادات لنفس المختبر (كل مختبر له إعداد واحد فقط)
            $table->unique('lab_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_schedules');
    }
};
