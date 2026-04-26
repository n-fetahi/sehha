// database/migrations/2024_01_01_000002_create_patients_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();                                          // INT PK Auto Increment
            $table->date('birth_date');                             // DATE تاريخ الميلاد
            $table->string('profile_picture', 255)->nullable();    // VARCHAR(255) nullable
            $table->string('blood_type', 5)->nullable();           // VARCHAR(5) nullable
            $table->decimal('height', 5, 2)->nullable();           // DECIMAL(5,2) nullable
            $table->decimal('weight', 5, 2)->nullable();           // DECIMAL(5,2) nullable

            // 🔗 المفتاح الأجنبي - علاقة 1:1 مع users
            $table->foreignId('user_id')
                  ->unique()                                       // لضمان علاقة 1:1
                  ->constrained('users')                           // FK → users.id
                  ->onDelete('cascade');                            // حذف تلقائي

            $table->timestamps();                                  // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
