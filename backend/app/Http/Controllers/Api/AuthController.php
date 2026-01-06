<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $auth): JsonResponse {
        $result = $auth->login(
            $request->email,
            $request->password
        );

        return response()->json($result);
    }

    public function register(RegisterRequest $request, AuthService $auth): JsonResponse {
        $result = $auth->register(
            $request->name,
            $request->email,
            $request->password
        );

        return response()->json($result, 201);
    }

    public function logout(Request $request, AuthService $auth): JsonResponse {
        $auth->logout($request->user());
        return response()->json(['status' => 'ok']);
    }
}
