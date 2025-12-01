<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    //Dang nhập
    public function login(Request $request){
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json(['error' => 'Email hoặc mật khẩu sai'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'ok' => true,
            'id' => $user->id,
            'token' => 'Bearer ' . $token,
            'email' => $user->email,
            'fullName' => $user->full_name,
            'role' => $user->role,
        ]);
    }
    //Dang ký
    public function register(Request $request){

    $exists = User::where('email', $request->email)->exists();

    if ($exists) {
        return response()->json([
            'error' => 'Email đã tồn tại, vui lòng chọn email khác'
        ], 409);
    }

    $user = User::create([
        'full_name' => $request->fullName,
        'email' => $request->email,
        'password_hash' => Hash::make($request->password),
        'role' => 'CUSTOMER',
    ]);

    return response()->json(['message' => 'Đăng ký thành công', 'ok' => true]);
    }
    public function profile() {
        return response()->json(Auth::guard('api')->user());
    }
    public function logout() {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }
    public function refreshToken() {
        $newToken = JWTAuth::refresh();
        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }
}
