<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;

class LocaleController extends Controller
{
    public function supported(): JsonResponse
    {
        return response()->json([
            'data' => [
                'default' => config('app.locale', 'en'),
                'fallback' => config('app.fallback_locale', 'en'),
                'supported' => ['en', 'km'],
            ],
        ]);
    }
}
