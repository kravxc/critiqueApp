<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Requests\UploadCoverRequest;
use App\Http\Resources\AllContentsResource;
use App\Http\Resources\ShowContentResource;
use App\Http\Resources\StoreContentResource;
use App\Models\Content;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContentsController extends Controller
{

    public function index(Request $request)
    {
        if ($request->has('show_trashed') && auth()->check() && auth()->user()->role_id == 1) {
            $showTrashed = $request->input('show_trashed', 'without');

            if ($showTrashed === 'with') {
                $contents = Content::withTrashed()->get();
            } elseif ($showTrashed === 'only') {
                $contents = Content::onlyTrashed()->get();
            } else {
                $contents = Content::all();
            }
        } else {
            $contents = Content::all();
        }

        return AllContentsResource::collection($contents);
    }


    public function lastContents()
    {
        $contents = Content::latest()->take(5)->get();

        return AllContentsResource::collection($contents);
    }


    public function dashboardStats()
    {
        $stats = [
            'total_content' => Content::count(),
            'total_content_with_trashed' => Content::withTrashed()->count(),
            'total_users' => User::count(),
            'total_reviews' => Review::count(),
        ];

        return response()->json([
            'data' => [
                'stats' => $stats
            ]
        ]);
    }


    public function show($id)
    {
        if (auth()->check() && auth()->user()->role_id == 1) {
            $content = Content::withTrashed()->with('reviews.user.role')->find($id);
        } else {
            $content = Content::with('reviews.user.role')->find($id);
        }

        if (!$content) {
            return response()->json([
                'message' => 'Релиз не найден'
            ], 404);
        }

        return new ShowContentResource($content);
    }

    public function toggleFavorite($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => [
                    'message' => 'Unauthorized'
                ]
            ], 401);
        }

        $content = Content::find($id);
        if (!$content) {
            return response()->json([
                'errors' => [
                    'content' => 'Content not found'
                ]
            ], 404);
        }

        if ($user->hasFavorited($content->id)) {
            $user->removeFromFavorites($content->id);
            $content->decrement('favorites_count');

            return response()->json([
                'data' => [
                    'message' => 'Content successfully removed from favorites'
                ]
            ], 200);
        } else {
            $user->addToFavorites($content->id);
            $content->increment('favorites_count');

            return response()->json([
                'data' => [
                    'message' => 'Content successfully added to favorites'
                ]
            ], 201);
        }
    }


    public function uploadCover(UploadCoverRequest $request, $id)
    {
        $data = $request->validated();

        $content = Content::withTrashed()->find($id);

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        if (auth()->id() != $content->added_by && auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $file = $data['cover_image'];

        if ($content->cover_image) {
            Storage::disk('yandex-s3')->delete($content->cover_image);
        }

        $filename = 'covers/content_' . $content->id . '/'
            . time() . '_'
            . uniqid() . '.'
            . $file->getClientOriginalExtension();

        Storage::disk('yandex-s3')->put($filename, file_get_contents($file));

        $content->cover_image = $filename;
        $content->save();

        return response()->json([
            'data' => [
                'message' => 'Обложка успешно загружена',
                'cover_url' => $content->cover_image_url,
                'content' => $content
            ]
        ]);
    }


    public function deleteCover($id)
    {
        $content = Content::withTrashed()->find($id);

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        if (auth()->id() != $content->added_by && auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        if (!$content->cover_image) {
            return response()->json([
                'errors' => [
                    'message' => 'Обложка не найдена'
                ]
            ], 404);
        }

        if ($content->deleteCoverImage()) {
            return response()->json([
                'data' => [
                    'message' => 'Обложка успешно удалена',
                    'content' => $content->fresh()
                ]
            ]);
        }

        return response()->json([
            'errors' => [
                'message' => 'Ошибка при удалении обложки'
            ]
        ], 500);
    }

    public function store(StoreContentRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        $content = Content::create([
            'title' => $data['title'],
            'artist' => $data['artist'],
            'music_type' => $data['music_type'],
            'genre' => $data['genre'],
            'release_date' => $data['release_date'],
            'label' => $data['label'],
            'added_by' => $user['id']
        ]);

        return new StoreContentResource($content);
    }

    public function update(UpdateContentRequest $request, $id)
    {
        $data = $request->validated();

        $content = Content::withTrashed()->find($id);

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        if (auth()->id() != $content->added_by && auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $content->update($data);

        return new StoreContentResource($content);
    }


    public function destroy($id)
    {
        $content = Content::find($id);

        if (!$content) {
            $trashedContent = Content::onlyTrashed()->find($id);
            if ($trashedContent) {
                return response()->json([
                    'errors' => [
                        'message' => 'Content is already deleted'
                    ]
                ], 400);
            }

            return response()->json([
                'errors' => [
                    'message' => 'Content not found'
                ]
            ], 404);
        }

        if (auth()->id() != $content->added_by && auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $content->delete();

        return response()->json([
            'data' => [
                'message' => 'Content successfully deleted',
                'content_id' => $content->id
            ]
        ], 200);
    }


    public function restore($id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $content = Content::onlyTrashed()->find($id);

        if (!$content) {
            return response()->json([
                'errors' => [
                    'message' => 'Deleted content not found'
                ]
            ], 404);
        }

        $content->restore();

        return response()->json([
            'data' => [
                'message' => 'Content successfully restored',
                'content' => new AllContentsResource($content)
            ]
        ], 200);
    }

    public function forceDelete($id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $content = Content::withTrashed()->find($id);

        if (!$content) {
            return response()->json([
                'errors' => [
                    'message' => 'Content not found'
                ]
            ], 404);
        }

        if ($content->cover_image) {
            Storage::disk('yandex-s3')->delete($content->cover_image);
        }

        $content->forceDelete();

        return response()->json([
            'data' => [
                'message' => 'Content permanently deleted'
            ]
        ], 200);
    }


    public function search(Request $request)
    {
        $genre = $request->input('genre', 'all');
        $musicType = $request->input('music_type');
        $sort = $request->input('sort', 'newest');
        $search = $request->input('search');
        $showTrashedFilter = $request->input('show_trashed_filter', 'without');

        if ($showTrashedFilter === 'with') {
            $query = Content::withTrashed();
        } elseif ($showTrashedFilter === 'only') {
            $query = Content::onlyTrashed();
        } else {
            $query = Content::query();
        }

        if ($search && !empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%");
            });
        }

        if ($musicType && $musicType !== 'all') {
            $query->where('music_type', $musicType);
        }

        if ($genre && $genre !== 'all') {
            $query->where('genre', $genre);
        }

        switch ($sort) {
            case 'oldest':
                $query->orderBy('release_date', 'asc');
                break;
            case 'popular':
                $query->orderBy('favorites_count', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('release_date', 'desc');
                break;
        }

        $contents = $query->get();
        $total_found = $contents->count();

        $data = [];
        foreach ($contents as $content) {
            $data[] = [
                'id' => $content->id,
                'title' => $content->title,
                'music_type' => $content->music_type,
                'artist' => $content->artist,
                'genre' => $content->genre,
                'release_date' => $content->release_date,
                'cover_image' => $content->cover_image,
                'cover_image_url' => $content->cover_image_url,
                'label' => $content->label,
                'favorites_count' => $content->favorites_count,
                'reviews_count' => $content->reviews_count,
                'added_by' => $content->added_by,
                'created_at' => $content->created_at,
                'updated_at' => $content->updated_at,
                'deleted_at' => $content->deleted_at,
                'is_trashed' => $content->trashed() ? true : false
            ];
        }

        return response()->json([
            'success' => true,
            'meta' => [
                'total_found' => $total_found,
                'filters_applied' => [
                    'search' => $search,
                    'genre' => $genre,
                    'music_type' => $musicType,
                    'sort' => $sort,
                    'show_trashed_filter' => $showTrashedFilter
                ]
            ],
            'data' => $data
        ]);
    }
}
