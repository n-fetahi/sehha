<?php

namespace App\Http\Controllers\Api\Clinics\Concerns;

use App\Models\Clinic;
use Illuminate\Http\Request;

trait ResolvesAuthenticatedClinic
{
    protected function resolveAuthenticatedClinic(Request $request): ?Clinic
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        if ($user->user_type === 'clinic') {
            return $user->ownedClinic;
        }

        if ($user->user_type === 'secretary') {
            return $user->secretaryClinic;
        }

        return null;
    }
}
