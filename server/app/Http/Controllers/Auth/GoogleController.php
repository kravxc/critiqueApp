<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        try {
            $state = bin2hex(random_bytes(16));

            Cache::put('google_oauth_state_' . $state, true, 600);

            $redirectUri = config('services.google.redirect');

            $redirectUrl = Socialite::driver('google')
                ->redirectUrl($redirectUri)  
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            if (strpos($redirectUrl, 'state=') === false) {
                $redirectUrl .= '&state=' . $state;
            }

            return response()->json([
                'redirect_url' => $redirectUrl,
                'state' => $state,
            ]);

        } catch (\Exception $e) {
            Log::error('Google redirect error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to initialize Google login'
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            if ($request->has('error')) {
                $error = $request->input('error');
                $errorDescription = $request->input('error_description', 'Unknown error');

                Log::warning('Google OAuth error callback', [
                    'error' => $error,
                    'description' => $errorDescription
                ]);

                return redirect()->away(
                    $frontendUrl . '/auth/google/callback?error=' . urlencode($error) .
                    '&message=' . urlencode($errorDescription)
                );
            }

            $state = $request->input('state');
            $cacheKey = 'google_oauth_state_' . $state;

            if (!$state || !Cache::get($cacheKey)) {
                Log::warning('Invalid or expired state parameter', ['state' => $state]);
                return redirect()->away(
                    $frontendUrl . '/auth/google/callback?error=invalid_state&message=' .
                    urlencode('Security validation failed. Please try again.')
                );
            }

            Cache::forget($cacheKey);

            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser || !$googleUser->getEmail()) {
                throw new \Exception('Could not retrieve user information from Google');
            }

            return $this->handleLoginOrRegister($googleUser, $frontendUrl);

        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->except('code')
            ]);

            return redirect()->away(
                $frontendUrl . '/auth/google/callback?error=auth_error&message=' .
                urlencode('Authentication failed: ' . $e->getMessage())
            );
        }
    }

    private function handleLoginOrRegister($googleUser, string $frontendUrl)
    {
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
                        'password' => bcrypt(Str::random(32)),
                        'google_id' => $googleUser->getId(),
                        'google_token' => $googleUser->token,
                        'google_refresh_token' => $googleUser->refreshToken,
                        'avatar' => $googleUser->getAvatar(),
                        'email_verified_at' => now(),
                        'role_id' => 2, // обычный пользователь
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
                'is_bind' => false,
            ];

            $encodedData = urlencode(json_encode($userData));
            $callbackUrl = $frontendUrl . '/auth/google/callback?data=' . $encodedData;

            Log::info('Google OAuth success', ['user_id' => $user->id, 'email' => $user->email]);

            return redirect()->away($callbackUrl);

        } catch (\Exception $e) {
            Log::error('Google user handling error: ' . $e->getMessage());
            return redirect()->away(
                $frontendUrl . '/auth/google/callback?error=user_error&message=' .
                urlencode('Error processing user data')
            );
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