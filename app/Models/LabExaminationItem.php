<?php

// app/Models/LabExaminationItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LabExaminationItem
 * @package App\Models
 * @property int $id المعرف
 * @property int $lab_id معرف المختبر
 * @property int $examination_item_id معرف عنصر الفحص
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read Lab $lab المختبر المرتبط
 * @property-read ExaminationItem $examinationItem عنصر الفحص المرتبط
 */
class LabExaminationItem extends Model
{
    protected $fillable = [
        'lab_id',
        'examination_item_id',
    ];

    /**
     * العلاقة مع المختبر.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * العلاقة مع عنصر الفحص.
     */
    public function examinationItem(): BelongsTo
    {
        return $this->belongsTo(ExaminationItem::class);
    }
}
