<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Wallet
 * @package App\Models
 * @property int $id المعرف
 * @property string $name اسم المحفظة (مثل: Apple Pay, Visa)
 * @property string $image مسار صورة المحفظة
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ClinicAppointment[] $appointments الحجوزات المرتبطة
 */
class Wallet extends Model
{
    protected $fillable = ['name', 'image'];

    /**
     * العلاقة مع حجوزات العيادات التي استخدمت هذه المحفظة.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(ClinicAppointment::class);
    }
}
