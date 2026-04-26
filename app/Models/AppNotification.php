<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AppNotification
 * @package App\Models
 * @property int $id المعرف
 * @property string $title عنوان الإشعار
 * @property string $content محتوى الإشعار
 * @property int $user_id معرف المستخدم المستلم
 * @property bool $is_delivered حالة التسليم
 * @property \Illuminate\Support\Carbon $created_at تاريخ الإنشاء
 * @property \Illuminate\Support\Carbon $updated_at تاريخ التعديل
 *
 * @property-read User $user المستلم
 */
class AppNotification extends Model
{
    protected $table = 'app_notifications'; // يضمن أن Laravel يستخدم اسم الجدول الصحيح

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'is_delivered',
    ];

    protected $casts = [
        'is_delivered' => 'boolean',
    ];

    /**
     * العلاقة مع المستخدم.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: الإشعارات غير المسلمة.
     */
    public function scopeUndelivered($query)
    {
        return $query->where('is_delivered', false);
    }

    /**
     * Scope: الإشعارات المسلمة.
     */
    public function scopeDelivered($query)
    {
        return $query->where('is_delivered', true);
    }

    /**
     * Scope: إشعارات مستخدم محدد.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * تعليم الإشعار كمُسلَّم.
     */
    public function markAsDelivered(): bool
    {
        return $this->update(['is_delivered' => true]);
    }
}
