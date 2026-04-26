<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;

trait FormatsTime
{
    private function formatTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}
