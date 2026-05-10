<?php

namespace App\Http\Controllers\Api\Patients;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PatientWalletController extends Controller
{
    /**
     * GET /api/patients/wallets
     * إرجاع قائمة بجميع المحافظ (العامة)
     */
    public function index(): JsonResponse
    {
        $wallets = Wallet::all()->map(function ($wallet) {
            return [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'image' => $wallet->image ? Storage::url($wallet->image) : null,
            ];
        });

        if ($wallets->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'لا توجد محافظ متاحة'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $wallets
        ]);
    }
}
