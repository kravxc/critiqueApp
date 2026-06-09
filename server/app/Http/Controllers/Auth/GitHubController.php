<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GitHubCallbackRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect(): JsonResponse
    {
        $state = bin2hex(random_bytes(16));

        cache()->put('github_state_' . $state, true, 600);

        $url = Socialite::driver('github')
            ->scopes(['read:user', 'user:email'])
            ->with(['state' => $state])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'redirect_url' => $url,
            'state' => $state
        ]);
    }

    public function callback(GitHubCallbackRequest $request)
    {
        try {
            $state = $request->input('state');

            if (!cache()->get('github_state_' . $state)) {
                $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
                return redirect()->away($frontendUrl . '/auth/github/callback?error=invalid_state&message=' . urlencode('Неверный параметр state'));
            }

            cache()->forget('github_state_' . $state);

            $githubUser = Socialite::driver('github')
                ->stateless()
                ->user();

            $bindToken = $request->input('bind_token');

            if (!$bindToken && $request->has('state')) {
                $stateParams = json_decode(base64_decode($request->state), true);
                $bindToken = $stateParams['bind_token'] ?? null;
            }

            if ($bindToken) {
                return $this->handleBinding($githubUser, $bindToken);
            }

            return $this->handleLoginOrRegister($githubUser);

        } catch (\Exception $e) {
            Log::error('GitHub OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/auth/github/callback?error=github_auth_error&message=' . urlencode($e->getMessage()));
        }
    }

    private function handleBinding($githubUser, string $bindToken)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($bindToken);

            if (!$personalAccessToken) {
                return redirect()->away($frontendUrl . '/auth/github/callback?error=user_not_found&message=' . urlencode('Пользователь не найден'));
            }

            $user = $personalAccessToken->tokenable;

            $existingUser = User::where('github_id', $githubUser->getId())->first();
            if ($existingUser && $existingUser->id !== $user->id) {
                return redirect()->away($frontendUrl . '/auth/github/callback?error=github_already_bound&message=' . urlencode('Этот GitHub аккаунт уже привязан к другому пользователю'));
            }

            $user->update([
                'github_id' => $githubUser->getId(),
                'github_token' => $githubUser->token,
                'github_refresh_token' => $githubUser->refreshToken,
                'avatar' => $githubUser->getAvatar() ?: $user->avatar,
            ]);

            $newToken = $user->createToken('auth-token')->plainTextToken;

            $userData = [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar,
                'github_id' => (string) $user->github_id,
                'token' => $newToken,
                'is_bind' => true,
                'message' => 'GitHub аккаунт успешно привязан'
            ];

            $encodedData = urlencode(json_encode($userData));
            return redirect()->away($frontendUrl . '/auth/github/callback?data=' . $encodedData);

        } catch (\Exception $e) {
            Log::error('GitHub bind error: ' . $e->getMessage());
            return redirect()->away($frontendUrl . '/auth/github/callback?error=bind_error&message=' . urlencode('Ошибка при привязке аккаунта'));
        }
    }

    private function handleLoginOrRegister($githubUser)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        try {
            $user = User::where('github_id', $githubUser->getId())->first();

            if (!$user) {
                $user = User::where('email', $githubUser->getEmail())->first();

                if ($user) {
                    $user->update([
                        'github_id' => $githubUser->getId(),
                        'github_token' => $githubUser->token,
                        'github_refresh_token' => $githubUser->refreshToken,
                        'avatar' => $githubUser->getAvatar() ?: $user->avatar,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $this->getUserName($githubUser),
                        'email' => $githubUser->getEmail(),
                        'password' => bcrypt(Str::random(16)),
                        'github_id' => $githubUser->getId(),
                        'github_token' => $githubUser->token,
                        'github_refresh_token' => $githubUser->refreshToken,
                        'avatar' => $githubUser->getAvatar(),
                        'role_id' => 2
                    ]);
                }
            } else {
                $user->update([
                    'github_token' => $githubUser->token,
                    'github_refresh_token' => $githubUser->refreshToken,
                    'avatar' => $githubUser->getAvatar() ?: $user->avatar,
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            $userData = [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar,
                'github_id' => (string) $user->github_id,
                'token' => $token,
                'is_bind' => false
            ];

            $encodedData = urlencode(json_encode($userData));
            return redirect()->away($frontendUrl . '/auth/github/callback?data=' . $encodedData);

        } catch (\Exception $e) {
            Log::error('GitHub login/register error: ' . $e->getMessage());
            return redirect()->away($frontendUrl . '/auth/github/callback?error=login_error&message=' . urlencode('Ошибка при входе через GitHub'));
        }
    }

    private function getUserName($githubUser): string
    {
        if (!empty($githubUser->getName())) {
            return $githubUser->getName();
        }
        if (!empty($githubUser->getNickname())) {
            return $githubUser->getNickname();
        }
        return explode('@', $githubUser->getEmail())[0];
    }
}
