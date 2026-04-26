<?php
// database/migrations/xxxx_xx_xx_create_examination_types_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_types', function (Blueprint $table) {
            $table->id(); // المعرف
            $table->string('name'); // الاسم - لا يقبل الفراغ
            $table->timestamps(); // تاريخ الانشاء والتعديل
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_types');
    }
};
