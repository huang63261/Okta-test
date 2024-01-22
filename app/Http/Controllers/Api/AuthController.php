<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except('login');
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        try {
            if (!$token =auth()->guard('api')->attempt($credentials)) {
                return response()->json(['status' => 0, 'message' => '無效的驗證資料'], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => '無法建立 Token'
            ], 500);
        }

        return response()->json(['status' => 1, 'token' => $token]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => 1]);
    }
}
