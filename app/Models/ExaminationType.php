<?php
// app/Models/ExaminationType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ExaminationType
 * @package App\Models
 * @property int $id المعرف
 * @property string $name اسم نوع الفحص (مثل: تحاليل دم، أشعة)
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 */
class ExaminationType extends Model
{
    protected $fillable = ['name'];

    /**
     * العلاقة مع عناصر الفحوصات التابعة لهذا النوع.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ExaminationItem::class);
    }
}
