<?php

// database/migrations/xxxx_xx_xx_create_examination_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_items', function (Blueprint $table) {
            $table->id(); // المعرف
            $table->string('name'); // الاسم - لا يقبل الفراغ

            // مفتاح أجنبي لنوع الفحص
            $table->foreignId('examination_type_id')
                  ->constrained('examination_types')
                  ->restrictOnDelete(); // يمنع حذف النوع إذا كان له عناصر مرتبطة (لأسباب طبية للحفاظ على السجلات)

            $table->timestamps(); // تاريخ الانشاء والتعديل
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_items');
    }
};
