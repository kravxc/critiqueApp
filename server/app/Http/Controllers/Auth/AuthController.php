<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\SignUpRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\SignUpResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $userData = $request->validated();

        if (Auth::attempt($userData)) {
            Auth::user()->tokens()->delete();

            $token = Auth::user()->createToken('auth_token')->plainTextToken;

            return new AuthResource($token);
        }else{
            return response()->json(
                [
                    'error' => 'Неверный email или пароль'
                ],
            401);
        }
    }
    public function register(SignUpRequest $request){

        $userData = $request->validated();

        $user = User::create($userData);

            $token = $user->createToken('auth_token')->plainTextToken;

            event(new Registered($user));
            return new SignUpResource(['token' => $token, 'user' => $user]);

    }
    public function logout(){
        if (auth('sanctum')->check()) {
            Auth::user()->tokens()->delete();
            return response(null, 204);
        } else {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => 'Unauthorized'
                ]
            ], 401);
        }
    }
}
