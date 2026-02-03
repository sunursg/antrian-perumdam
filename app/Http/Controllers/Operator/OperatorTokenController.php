<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorTokenController extends Controller
{
    public function issue(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user?->hasAnyRole(['ADMIN', 'SUPER_ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses token hanya untuk ADMIN / SUPER_ADMIN.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        if (!method_exists($user, 'createToken')) {
            return response()->json([
                'success' => false,
                'message' => 'Sanctum belum terpasang. Install laravel/sanctum untuk membuat token.',
                'data' => null,
                'errors' => null,
            ], 500);
        }

        $token = $user->createToken('operator-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token dibuat.',
            'data' => ['token' => $token],
            'errors' => null,
        ]);
    }
}
