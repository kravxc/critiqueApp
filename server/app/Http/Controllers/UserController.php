<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\SearchUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Http\Resources\EditProfileResource;
use App\Http\Resources\UploadAvatarResource;
use App\Http\Resources\UserSearchResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends Controller
{
    public function editProfile(EditProfileRequest $request)
    {
        $user = auth()->user();

        $data = $request->validated();

        $user->update($data);

        return new EditProfileResource($user);
    }

    public function editPassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Текущий пароль неверен'
            ], 400);
        }

        $user->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Пароль успешно изменен'
            ]
        ]);
    }

    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $data = $request->validated();

        $user = auth()->user();

        $file = $data['avatar'];

        if ($user->avatar) {
            Storage::disk('yandex-s3')->delete($user->avatar);
        }
        $filename = 'avatars/user_' . $user->id
            . '/' . time()
            . '_' . uniqid() . '.'
            . $file->getClientOriginalExtension();

        Storage::disk('yandex-s3')->put($filename, file_get_contents($file));

        $user->avatar = $filename;
        $user->save();

        return new UploadAvatarResource($user);
    }

    public function deleteAvatar()
    {
        $user = auth()->user();

        if (!$user->avatar) {
            return response()->json([
                'error' => [
                    'message' => 'Аватар не найден'
                ]
            ], 404);
        }

        if (Storage::disk('yandex-s3')->delete($user->avatar)) {
            $user->avatar = null;
            $user->save();
            return response()->json([
                'message' => 'Аватар успешно удален'
            ]);
        }

        return response()->json([
            'error' => [
                'message' => 'Ошибка при удалении аватара'
            ]
        ], 500);
    }

    public function getAvatar($id)
    {
        $user = User::find($id);

        if (!$user || !$user->avatar_url) {
            return response()->json([
                'error' => [
                    'message' => 'Аватар не найден'
                ]
            ], 404);
        }

        return response()->json([
            'data' => [
                'avatar' => $user->avatar_url
            ]
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, $id)
    {
        $user = auth()->user();

        if ($user->role->name === 'admin') {
            $updateUser = User::find($id);

            if (!$updateUser) {
                return response()->json([
                    'error' => ['message' => 'Пользователь не найден']
                ], 404);
            }

            if ($updateUser->id === 1) {
                return response()->json([
                    'error' => [
                        'message' => 'Нельзя поменять роль главного администратора'
                    ]
                ], ResponseAlias::HTTP_CONFLICT);
            }

            $updateUser->update($request->validated());

            return response()->json([
                'data' => [
                    'message' => 'Роль успешно обновлена!'
                ]
            ]);
        }

        return response()->json([
            'error' => ['message' => 'Forbidden']
        ], 403);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $deleteUser = User::withTrashed()->find($id);

        if (!$deleteUser) {
            return response()->json([
                'error' => ['message' => 'Пользователь не найден']
            ], 404);
        }

        if ($deleteUser->id === $user->id) {
            return response()->json([
                'error' => ['message' => 'Нельзя удалить самого себя']
            ], ResponseAlias::HTTP_CONFLICT);
        }

        if ($deleteUser->id === 1) {
            return response()->json([
                'error' => ['message' => 'Нельзя удалить главного админа']
            ], ResponseAlias::HTTP_CONFLICT);
        }

        if ($deleteUser->trashed()) {
            return response()->json([
                'error' => ['message' => 'Пользователь уже удален']
            ], ResponseAlias::HTTP_CONFLICT);
        }

        $deleteUser->delete();

        return response()->json([
            'data' => ['message' => 'Пользователь успешно удален (soft delete)']
        ]);
    }

    public function restore($id)
    {
        $currentUser = auth()->user();

        if ($currentUser->role_id != 1) {
            return response()->json([
                'error' => ['message' => 'Доступ запрещен']
            ], 403);
        }

        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'error' => ['message' => 'Пользователь не найден']
            ], 404);
        }

        if (!$user->trashed()) {
            return response()->json([
                'error' => ['message' => 'Пользователь не был удален']
            ], ResponseAlias::HTTP_CONFLICT);
        }

        $user->restore();

        return response()->json([
            'data' => ['message' => 'Пользователь успешно восстановлен']
        ]);
    }

    public function forceDelete($id)
    {
        $currentUser = auth()->user();

        if ($currentUser->role_id != 1) {
            return response()->json([
                'error' => ['message' => 'Доступ запрещен']
            ], 403);
        }

        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'error' => ['message' => 'Пользователь не найден']
            ], 404);
        }

        if ($user->id === 1) {
            return response()->json([
                'error' => ['message' => 'Нельзя удалить главного администратора']
            ], ResponseAlias::HTTP_CONFLICT);
        }

        if ($user->avatar) {
            Storage::disk('yandex-s3')->delete($user->avatar);
        }

        $user->forceDelete();

        return response()->json([
            'data' => ['message' => 'Пользователь полностью удален из базы данных']
        ]);
    }

    public function getUserByToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => ['message' => 'User not found']
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'preferences' => $user->preferences,
                'github-auth' => (bool)$user->github_id,
                'google-auth' => (bool)$user->google_id,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    public function bindGitHub(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'github_id' => 'required|string',
            'github_token' => 'required|string',
            'github_refresh_token' => 'nullable|string'
        ]);

        $existingUser = User::where('github_id', $request->github_id)->first();
        if ($existingUser && $existingUser->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Этот GitHub аккаунт уже привязан к другому пользователю'
            ], 400);
        }

        $user->update([
            'github_id' => $request->github_id,
            'github_token' => $request->github_token,
            'github_refresh_token' => $request->github_refresh_token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'GitHub аккаунт успешно привязан',
            'data' => $user
        ]);
    }

    public function bindGoogle(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'google_id' => 'required|string',
            'google_token' => 'required|string',
            'google_refresh_token' => 'nullable|string'
        ]);

        $existingUser = User::where('google_id', $request->google_id)->first();
        if ($existingUser && $existingUser->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Этот Google аккаунт уже привязан к другому пользователю'
            ], 400);
        }

        $user->update([
            'google_id' => $request->google_id,
            'google_token' => $request->google_token,
            'google_refresh_token' => $request->google_refresh_token
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Google аккаунт успешно привязан',
            'data' => $user
        ]);
    }

    public function unbindGitHub(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'github_id' => null,
            'github_token' => null,
            'github_refresh_token' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'GitHub аккаунт отвязан'
        ]);
    }

    public function unbindGoogle(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Google аккаунт отвязан'
        ]);
    }

    public function search(SearchUserRequest $request)
    {
        $validated = $request->validated();

        $currentUser = auth()->user();

        $search = $validated['search'] ?? null;
        $roleId = $validated['role_id'] ?? null;
        $trashed = $validated['trashed'] ?? 'without';
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = $validated['per_page'] ?? 15;
        $hasGithub = $validated['has_github'] ?? null;
        $hasGoogle = $validated['has_google'] ?? null;
        $hasAvatar = $validated['has_avatar'] ?? null;
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        $query = User::query()->with('role');

        if ($sortBy === 'reviews_count') {
            $query->withCount('reviews');
        }

        if ($trashed === 'with' || $trashed === 'only') {
            if (!$currentUser || $currentUser->role_id != 1) {
                return response()->json([
                    'error' => 'Доступ запрещен для просмотра удаленных пользователей'
                ], 403);
            }

            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        }

        if ($search && !empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        if ($hasGithub !== null) {
            if ($hasGithub) {
                $query->whereNotNull('github_id');
            } else {
                $query->whereNull('github_id');
            }
        }

        if ($hasGoogle !== null) {
            if ($hasGoogle) {
                $query->whereNotNull('google_id');
            } else {
                $query->whereNull('google_id');
            }
        }

        if ($hasAvatar !== null) {
            if ($hasAvatar) {
                $query->whereNotNull('avatar');
            } else {
                $query->whereNull('avatar');
            }
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        switch ($sortBy) {
            case 'name':
            case 'email':
            case 'created_at':
            case 'updated_at':
                $query->orderBy($sortBy, $sortOrder);
                break;
            case 'reviews_count':
                $query->orderBy('reviews_count', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $users = $query->paginate($perPage);

        $stats = [];
        if ($currentUser && $currentUser->role_id == 1) {
            $stats = [
                'total_users' => User::count(),
                'total_with_trashed' => User::withTrashed()->count(),
                'total_only_trashed' => User::onlyTrashed()->count(),
                'total_by_role' => $this->getUserCountByRole(),
                'total_with_socials' => [
                    'github' => User::whereNotNull('github_id')->count(),
                    'google' => User::whereNotNull('google_id')->count(),
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'stats' => $stats,
                'filters_applied' => [
                    'search' => $search,
                    'role_id' => $roleId,
                    'trashed' => $trashed,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                    'has_github' => $hasGithub,
                    'has_google' => $hasGoogle,
                    'has_avatar' => $hasAvatar,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ]
            ],
            'data' => UserSearchResource::collection($users)
        ]);
    }

    private function getUserCountByRole()
    {
        $roles = Role::all();
        $stats = [];

        foreach ($roles as $role) {
            $stats[$role->name] = User::where('role_id', $role->id)->count();
        }

        return $stats;
    }
}
