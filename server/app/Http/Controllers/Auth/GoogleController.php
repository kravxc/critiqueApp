<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleCallbackRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): JsonResponse
    {
        $state = bin2hex(random_bytes(16));

        cache()->put('google_state_' . $state, true, 600);

        $url = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['state' => $state])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'success' => true,
            'redirect_url' => $url,
            'state' => $state,
        ]);
    }

    public function callback(GoogleCallbackRequest $request)
    {
        try {
            $state = $request->input('state');

            if (!Cache::get('google_state_' . $state)) {
                $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
                return redirect()->away($frontendUrl . '/auth/google/callback?error=invalid_state&message=' . urlencode('Invalid state parameter'));
            }

            Cache::forget('google_state_' . $state);

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $bindToken = $request->input('bind_token');

            if (!$bindToken && $request->has('state')) {
                $stateParams = json_decode(base64_decode($request->state), true);
                $bindToken = $stateParams['bind_token'] ?? null;
            }

            if ($bindToken) {
                return $this->handleBinding($googleUser, $bindToken);
            }

            return $this->handleLoginOrRegister($googleUser);

        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);

            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/auth/google/callback?error=google_auth_error&message=' . urlencode($e->getMessage()));
        }
    }

    private function handleBinding($googleUser, string $bindToken)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($bindToken);

            if (!$personalAccessToken) {
                return redirect()->away($frontendUrl . '/auth/google/callback?error=user_not_found&message=' . urlencode('Пользователь не найден'));
            }

            $user = $personalAccessToken->tokenable;

            $existingUser = User::where('google_id', $googleUser->getId())->first();
            if ($existingUser && $existingUser->id !== $user->id) {
                return redirect()->away($frontendUrl . '/auth/google/callback?error=google_already_bound&message=' . urlencode('Этот Google аккаунт уже привязан к другому пользователю'));
            }

            $user->update([
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            ]);

            $newToken = $user->createToken('auth-token')->plainTextToken;

            $userData = [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar,
                'google_id' => (string) $user->google_id,
                'token' => $newToken,
                'is_bind' => true,
                'message' => 'Google аккаунт успешно привязан'
            ];

            $encodedData = urlencode(json_encode($userData));
            return redirect()->away($frontendUrl . '/auth/google/callback?data=' . $encodedData);

        } catch (\Exception $e) {
            Log::error('Google bind error: ' . $e->getMessage());
            return redirect()->away($frontendUrl . '/auth/google/callback?error=bind_error&message=' . urlencode('Ошибка при привязке аккаунта'));
        }
    }

    private function handleLoginOrRegister($googleUser)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            $user = User::where('google_id', $googleUser->getId())->first();

            if (!$user) {
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'google_token' => $googleUser->token,
                        'google_refresh_token' => $googleUser->refreshToken,
                        'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $this->getUserName($googleUser),
                        'email' => $googleUser->getEmail(),
                        'password' => bcrypt(Str::random(16)),
                        'google_id' => $googleUser->getId(),
                        'google_token' => $googleUser->token,
                        'google_refresh_token' => $googleUser->refreshToken,
                        'avatar' => $googleUser->getAvatar(),
                        'email_verified_at' => now(),
                        'role_id' => 2,
                    ]);
                }
            } else {
                $user->update([
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            $userData = [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar,
                'google_id' => (string) $user->google_id,
                'token' => $token,
                'is_bind' => false
            ];

            $encodedData = urlencode(json_encode($userData));
            return redirect()->away($frontendUrl . '/auth/google/callback?data=' . $encodedData);

        } catch (\Exception $e) {
            Log::error('Google login/register error: ' . $e->getMessage());
            return redirect()->away($frontendUrl . '/auth/google/callback?error=login_error&message=' . urlencode('Ошибка при входе через Google'));
        }
    }

    private function getUserName($googleUser): string
    {
        $name = $googleUser->getName();
        if (!empty($name)) {
            return $name;
        }
        $email = $googleUser->getEmail();
        if (!empty($email)) {
            return explode('@', $email)[0];
        }
        return 'User_' . Str::random(8);
    }
}
