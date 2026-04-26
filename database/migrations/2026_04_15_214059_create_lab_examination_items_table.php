<?php
// database/migrations/xxxx_xx_xx_create_lab_examination_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_examination_items', function (Blueprint $table) {
            $table->id(); // المعرف

            $table->foreignId('lab_id')
                  ->constrained('labs')
                  ->cascadeOnDelete(); // إذا حُذف المختبر تُحذف خدماته تلقائياً

            $table->foreignId('examination_item_id')
                  ->constrained('examination_items')
                  ->cascadeOnDelete(); // إذا حُذف عنصر الفحص تُحذف ارتباطاته

            $table->timestamps(); // تاريخ الانشاء والتعديل

            // منع تكرار إضافة نفس عنصر الفحص لنفس المختبر
            $table->unique(['lab_id', 'examination_item_id'], 'lab_examination_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_examination_items');
    }
};
