<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use App\Models\HealthContent;

class HealthContentController extends Controller
{
    public function index()
    {
        $healthContents = HealthContent::latest()
            ->get(['id', 'title', 'content', 'image']);

        return response()->json([
            'status' => 200,
            'data' => $healthContents,
        ]);
    }
}
