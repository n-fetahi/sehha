<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_appointments', function (Blueprint $table) {
            $table->date('appointment_date')->nullable()->change();
            $table->time('appointment_time')->nullable()->change();
            $table->decimal('booking_fee', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clinic_appointments', function (Blueprint $table) {
            $table->date('appointment_date')->nullable(false)->change();
            $table->time('appointment_time')->nullable(false)->change();
            $table->decimal('booking_fee', 10, 2)->default(0.00)->nullable(false)->change();
        });
    }
};
