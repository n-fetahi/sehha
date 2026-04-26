<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();                       // المعرف
            $table->string('name');             // الاسم (لا يقبل الفراغ)
            $table->string('image');            // مسار الصورة (لا يقبل الفراغ)
            $table->timestamps();               // تاريخ الانشاء والتعديل
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
