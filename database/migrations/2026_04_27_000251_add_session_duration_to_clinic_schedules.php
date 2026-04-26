<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_schedules', function (Blueprint $table) {
            // إضافة عمود session_duration مع قيمة افتراضية 0 لتجنب خطأ SQLite
            $table->unsignedSmallInteger('session_duration')->after('end_time')->default(0);

            // حذف الأعمدة القديمة
            $table->dropColumn(['consultation_duration', 'follow_up_duration']);
            // follow_up_period يبقى كما هو
        });
    }

    public function down(): void
    {
        Schema::table('clinic_schedules', function (Blueprint $table) {
            // إعادة الأعمدة المحذوفة
            $table->unsignedSmallInteger('consultation_duration');
            $table->unsignedSmallInteger('follow_up_duration');

            // حذف العمود الجديد
            $table->dropColumn('session_duration');
        });
    }
};
