<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\ToggleLikeRequest;
use App\Http\Resources\AllContentsResource;
use App\Http\Resources\AllReviewResource;
use App\Http\Resources\LastUserReviewsResource;
use App\Http\Resources\PopularReviewResource;
use App\Http\Resources\StoreReviewResource;
use App\Models\Review;
use App\Models\Review_Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\error;

class ReviewsController extends Controller
{
    public function index()
    {
        return AllReviewResource::collection(Review::all());
    }

    public function popularReviews()
    {
        $reviews = Review::with('user')
        ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->take(3)
            ->get();

        return PopularReviewResource::collection($reviews);
    }
    public function lastUserReviews()
    {
        $user = auth()->user();
        if (!$user){
            return response()->json([
                'message' => 'Unauthenticated'
            ],401);
        }

        $reviews = $user->reviews()->with(['content','likes'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        if ($reviews->count() < 0){
            return response()->json([
                'message' => 'Reviews not found'
            ],404);
        }
    return new LastUserReviewsResource($reviews);

    }
    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $review = Review::withTrashed()->find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $isAdmin = $user->role && $user->role->name === 'admin';
        $isOwner = $review->user_id === $user->id;

        if (!$isAdmin && !$isOwner) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($review->trashed()) {
            if ($isAdmin) {
                $review->likes()->delete();
                $review->forceDelete();
                return response()->json(['message' => 'Review force deleted successfully'], 200);
            }
            return response()->json(['message' => 'Review is already deleted'], 400);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }
    public function store(StoreReviewRequest $request)
    {
        $user = auth()->user();
        if(!$user){
            return response()->json([
                'message' => 'Unauthorized'
            ],401);
        }
        $data = $request->validated();

        $data['user_id'] = $user->id;
        $review = Review::create($data);

        return new StoreReviewResource($review);
    }
    public function toggleLike(ToggleLikeRequest $request, $id)
    {
        $data = $request->validated();

        $user = auth()->user();
        if(!$user){
            return response()->json([
                'message' => 'Не авторизован'
            ],401);
        }

        $review = Review::find($id);
        if(!$review){
            return response()->json([
                'message' => 'Отзыв не найден'
            ],404);
        }

        $likeType = $data['like_type'] ?? null;

        $userRole = $user->role->name ?? null;

        if ($likeType == 'author_like') {
            if ($userRole !== 'author') {
                return response()->json([
                    'message' => 'Только авторы могут ставить авторские лайки'
                ],403);
            }

            $existingLike = Review_Like::where('reviews_id', $id)
                ->where('user_id', $user->id)
                ->where('type', 'author_like')
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                return response()->json([
                    'message' => 'Авторский лайк удален'
                ],200);
            }

            Review_Like::create([
                'reviews_id' => $id,
                'user_id' => $user->id,
                'type' => 'author_like'
            ]);

            return response()->json([
                'message' => 'Поставлен авторский лайк'
            ],200);

        } elseif ($likeType == 'like') {
            if ($userRole === 'author') {
                return response()->json([
                    'message' => 'Авторы не могут ставить обычные лайки'
                ],403);
            }

            $existingLike = Review_Like::where('reviews_id', $id)
                ->where('user_id', $user->id)
                ->where('type', 'like')
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                return response()->json([
                    'message' => 'Лайк удален'
                ],200);
            }

            Review_Like::create([
                'reviews_id' => $id,
                'user_id' => $user->id,
                'type' => 'like'
            ]);

            return response()->json([
                'message' => 'Лайк поставлен'
            ],200);
        }

        return response()->json([
            'message' => 'Некорректный тип лайка'
        ],400);
    }
    public function restore($id)
    {
        $user = auth()->user();

        if (!$user || $user->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $review = Review::onlyTrashed()->find($id);

        if (!$review) {
            return response()->json([
                "error" => [
                    'message' => 'Deleted review not found'
                ]
            ], 404);
        }

        $review->restore();

        return response()->json([
            'data' => [
                'message' => 'Review successfully restored',
                'review' => new AllReviewResource($review)
            ]
        ], 200);
    }
    public function forceDelete($id)
    {
        $user = auth()->user();

        if (!$user || $user->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $review = Review::withTrashed()->find($id);

        if (!$review) {
            return response()->json([
                'error' => [
                    'message' => 'Review not found'
                ]
            ], 404);
        }

        Review_Like::where('reviews_id', $id)->delete();

        $review->forceDelete();

        return response()->json([
            'data' => [
                'message' => 'Review permanently deleted successfully'
            ]
        ], 200);
    }
    public function search(SearchRequest $request)
    {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $userName = $validated['user_name'] ?? null;
        $trashed = $validated['trashed'] ?? 'without';
        $contentId = $validated['content_id'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = $validated['per_page'] ?? 15;

        $query = Review::with(['user', 'content', 'regularLikes.user', 'authorLikes.user'])
            ->withCount(['likes as total_likes_count'])
            ->withCount([
                'likes as regular_likes_count' => function ($q) {
                    $q->where('type', 'like');
                }
            ])
            ->withCount([
                'likes as author_likes_count' => function ($q) {
                    $q->where('type', 'author_like');
                }
            ]);

        $user = auth()->user();

        if ($trashed === 'only') {
            $query->onlyTrashed();
        } elseif ($trashed === 'with') {
            $query->withTrashed();
        } else {
            $query->whereNull('reviews.deleted_at');
        }

        if ($search && !empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('text', 'like', "%{$search}%");
            });
        }

        if ($userName && !empty($userName)) {
            $query->whereHas('user', function ($q) use ($userName) {
                $q->where('name', 'like', "%{$userName}%");
            });
        }

        if ($contentId) {
            $query->where('content_id', $contentId);
        }

        switch ($sortBy) {
            case 'likes_count':
                $query->orderBy('total_likes_count', $sortOrder);
                break;
            case 'title':
            case 'created_at':
            case 'updated_at':
                $query->orderBy($sortBy, $sortOrder);
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $reviews = $query->paginate($perPage);

        $stats = [];
        if ($user && $user->role_id == 1) {
            $stats = [
                'total_found' => $reviews->total(),
                'total_with_trashed' => Review::withTrashed()->count(),
                'total_only_trashed' => Review::onlyTrashed()->count(),
            ];
        } else {
            $stats = [
                'total_found' => $reviews->total(),
                'total_with_trashed' => null,
                'total_only_trashed' => null,
            ];
        }

        return response()->json([
            'success' => true,
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'stats' => $stats,
                'filters_applied' => [
                    'search' => $search,
                    'user_name' => $userName,
                    'content_id' => $contentId,
                    'trashed' => $trashed,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]
            ],
            'data' => AllReviewResource::collection($reviews)
        ]);
    }
    private function getTotalWithTrashed($user)
    {
        if (!$user || $user->role_id != 1) {
            return null;
        }
        return Review::withTrashed()->count();
    }

    private function getTotalOnlyTrashed($user)
    {
        if (!$user || $user->role_id != 1) {
            return null;
        }
        return Review::onlyTrashed()->count();
    }
}
