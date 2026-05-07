<?php

namespace App\Http\Controllers\Api\Global;

use App\Http\Controllers\Controller;
use App\Models\HealthContent;

class HealthContentController extends Controller
{
    public function index()
    {
        $healthContents = HealthContent::latest()
            ->get(['id', 'title', 'content', 'image'])
            ->map(function (HealthContent $healthContent) {
                return [
                    'id' => $healthContent->id,
                    'title' => $healthContent->title,
                    'content' => $healthContent->content,
                    'image' => $healthContent->image ? asset($healthContent->image) : null,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => $healthContents,
        ]);
    }
}
