<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->integer('years_of_experience')->nullable(); // اختياري
            $table->text('bio')->nullable(); // اختياري
            $table->string('location', 255);
            $table->string('license_number', 50)->unique();
            $table->string('license')->nullable(); // مسار الصورة أو PDF
            $table->enum('license_status', ['pending','approved','rejected'])->default('pending'); //حالة ترخيص مزاولة المهنة
            $table->string('commercial_reg')->nullable(); // مسار الصورة أو PDF
            $table->enum('commercial_reg_status', ['pending','approved','rejected'])->default('pending'); // حالة ترخيص السجل التجاري
            $table->text('rejection_reason')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
             $table->foreignId('secretary_id')->nullable()
          ->constrained('users')->nullOnDelete();
            $table->foreignId('medical_department_id')->constrained('medical_departments')->cascadeOnDelete();
            $table->string('profile_picture')->nullable();
            $table->decimal('rating', 2, 1)->default(0); // نطاق 0.0 إلى 5.0

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
