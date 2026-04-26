<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($data = [], $message = 'success')
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    public function error($message = 'error', $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }
}