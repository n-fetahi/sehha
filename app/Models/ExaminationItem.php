<?php
// app/Models/ExaminationItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ExaminationItem
 * @package App\Models
 * @property int $id المعرف
 * @property string $name اسم عنصر الفحص (مثل: هيموجلوبين، سكر صائم)
 * @property int $examination_type_id معرف نوع الفحص
 * @property \Illuminate\Support\Carbon $created_at تاريخ الانشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read ExaminationType $examinationType نوع الفحص المرتبط
 */
class ExaminationItem extends Model
{
    protected $fillable = [
        'name',
        'examination_type_id',
    ];

    /**
     * العلاقة مع نوع الفحص الذي ينتمي إليه هذا العنصر.
     */
    public function examinationType(): BelongsTo
    {
        return $this->belongsTo(ExaminationType::class);
    }

    /**
     * المختبرات التي تقدم عنصر الفحص هذا.
     */
    public function labs()
    {
        return $this->belongsToMany(Lab::class, 'lab_examination_items')
                    ->withTimestamps();
    }

    public function examinationRequests()
    {
        return $this->hasMany(ExaminationRequest::class);
    }
}
