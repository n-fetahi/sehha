<?php

namespace App\Http\Controllers\Api\Labs;

use App\Http\Controllers\Controller;
use App\Models\ExaminationItem;
use App\Models\ExaminationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LabExaminationController extends Controller
{
    /**
     * GET /api/labs/examination-types
     * قائمة أنواع الفحوصات (للتبويبات)
     */
    public function getExaminationTypes(): JsonResponse
    {
        $types = ExaminationType::select('id', 'name')->get();

        return response()->json([
            'status' => 200,
            'data' => $types
        ]);
    }

    /**
     * GET /api/labs/examination-types/{examination_type_id}
     * جلب فحوصات نوع معين مع is_selected بناءً على مختبر المستخدم
     */
    public function getExaminationsByType(Request $request, int $examination_type_id): JsonResponse
    {
        $user = $request->user();
        $lab = $user->ownedLab;

        if (!$lab) {
            return response()->json([
                'status' => 400,
                'message' => 'المختبر غير موجود'
            ], 400);
        }

        // جلب جميع الفحوصات التابعة لهذا النوع
        $examinations = ExaminationItem::where('examination_type_id', $examination_type_id)
            ->select('id', 'name')
            ->get();

        // جلب ids الفحوصات التي يقدمها هذا المختبر حالياً
        $providedIds = $lab->examinationItems()->pluck('examination_item_id')->toArray();

        // إضافة is_selected لكل فحص
        $data = $examinations->map(function ($item) use ($providedIds) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'is_selected' => in_array($item->id, $providedIds)
            ];
        });

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    /**
     * POST /api/labs/examination-items
     * مزامنة الفحوصات التي يقدمها المختبر (استبدال كامل)
     */
   public function syncExaminationItems(Request $request): JsonResponse
{
    $user = $request->user();
    $lab = $user->ownedLab;

    if (!$lab) {
        return response()->json([
            'status' => 400,
            'message' => 'المختبر غير موجود'
        ], 400);
    }

    $validator = Validator::make($request->all(), [
        'examination_type_id' => ['required', 'integer', 'exists:examination_types,id'],
        'examination_item_ids' => ['required', 'array'],
        'examination_item_ids.*' => ['integer', 'exists:examination_items,id']
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => 'بيانات غير صالحة',
            'errors' => $validator->errors()
        ], 400);
    }

    $typeId = $request->examination_type_id;
    $newItemIds = $request->examination_item_ids;

    // التأكد أن جميع ids المرسلة تنتمي إلى الـ type المطلوب
    $validItems = ExaminationItem::where('examination_type_id', $typeId)
        ->whereIn('id', $newItemIds)
        ->pluck('id')
        ->toArray();

    if (count($validItems) !== count($newItemIds)) {
        return response()->json([
            'status' => 400,
            'message' => 'بعض الفحوصات المرسلة لا تنتمي إلى نوع الفحص المحدد'
        ], 400);
    }

    // جلب جميع ids الفحوصات التي يقدمها المختبر حالياً، ولكن فقط من هذا النوع
    $currentItemIdsOfThisType = $lab->examinationItems()
        ->where('examination_type_id', $typeId)
        ->pluck('examination_items.id')
        ->toArray();

    // العناصر المراد إضافتها (موجودة في الجديد وليست في الحالي)
    $toAttach = array_diff($validItems, $currentItemIdsOfThisType);
    // العناصر المراد حذفها (موجودة في الحالي وليست في الجديد)
    $toDetach = array_diff($currentItemIdsOfThisType, $validItems);

    // تنفيذ الإضافة والحذف على العلاقة فقط لهذه العناصر
    if (!empty($toAttach)) {
        $lab->examinationItems()->attach($toAttach);
    }
    if (!empty($toDetach)) {
        $lab->examinationItems()->detach($toDetach);
    }

    return response()->json([
        'status' => 200,
        'message' => 'تم تحديث الفحوصات بنجاح'
    ]);
}
}
