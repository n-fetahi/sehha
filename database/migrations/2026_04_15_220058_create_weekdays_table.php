<?php
// database/migrations/xxxx_xx_xx_create_weekdays_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekdays', function (Blueprint $table) {
            $table->id(); // المعرف
            $table->string('name'); // اسم اليوم (السبت, الاحد, ...)
            $table->timestamps(); // تاريخ الانشاء والتعديل
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekdays');
    }
};
