<?php

namespace App\Domains\Auth\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. التحقق من البيانات المدخلة
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. البحث عن المستخدم
        $user = User::where('email', $request->email)->first();

        // 3. التأكد من صحة الباسورد
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات الدخول غير صحيحة.'
            ], 401);
        }

        // 4. توليد التوكن (المفتاح المشفر)
        $token = $user->createToken('admin_access_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token, // ده اللي هتبعته في الـ Header بعد كدة
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        // حذف التوكن الحالي بس
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}