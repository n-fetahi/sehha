<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();                           // المعرف
            $table->string('title');                // العنوان
            $table->text('content');                // المحتوى
            $table->foreignId('user_id')            // المستخدم المستهدف
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->boolean('is_delivered')->default(false); // حالة التسليم
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_delivered');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
