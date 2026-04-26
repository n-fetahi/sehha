<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id(); // معرف المختبر
            $table->string('name', 100); // اسم المختبر
            $table->string('phone', 20); // رقم هاتف المختبر
            $table->string('medical_director', 100)->nullable(); // اسم المسؤول الطبي
            $table->string('location', 255); // موقع المختبر
            $table->string('license_number', 50)->unique(); // رقم الترخيص الطبي
            $table->string('license', 255); // مسار ملف الترخيص (صورة أو PDF)
            $table->enum('license_status', ['pending','approved','rejected'])->default('pending'); //   حالة الترخيص الطبي
            $table->string('commercial_reg', 255); // مسار السجل التجاري (صورة أو PDF)
            $table->enum('commercial_reg_status', ['pending','approved','rejected'])->default('pending'); // حالة ترخيص السجل التجاري
            $table->text('rejection_reason')->nullable(); // سبب الرفض
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // رابط المستخدم
            $table->string('profile_picture', 255)->nullable(); // صورة المختبر
            $table->decimal('rating', 2, 1)->default(0); // نطاق 0.0 إلى 5.0

            $table->timestamps(); // created_at و updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
