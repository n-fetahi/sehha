<?php
// database/migrations/xxxx_xx_xx_create_examination_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_appointment_id')
                  ->nullable()
                  ->constrained('clinic_appointments')
                  ->nullOnDelete();

            $table->foreignId('lab_appointment_id')
                  ->nullable()
                  ->constrained('lab_appointments')
                  ->nullOnDelete();

            $table->foreignId('examination_item_id')
                  ->constrained('examination_items')
                  ->cascadeOnDelete();

            $table->enum('status', ['pending', 'in_progress', 'completed'])
                  ->default('pending');

            $table->timestamps();

            $table->index(['clinic_appointment_id', 'lab_appointment_id'], 'exam_req_app_idx');
        });

        // قيد لضمان وجود رابط واحد على الأقل
       // تطبيق القيد فقط إذا لم تكن قاعدة البيانات SQLite
if (DB::connection()->getDriverName() !== 'sqlite') {
    DB::statement("ALTER TABLE examination_requests ADD CONSTRAINT check_request_source CHECK ((clinic_appointment_id IS NOT NULL) OR (lab_appointment_id IS NOT NULL))");
}
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_requests');
    }
};
