<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ContentsController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

Route::post('signup', [AuthController::class, 'register']);

Route::get('contents', [ContentsController::class, 'index']);

Route::get('reviews/popular', [ReviewsController::class, 'popularReviews']);

Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('auth.github');
Route::get('auth/github/callback', [GitHubController::class, 'callback']);

Route::get('auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
Route::post('/userinfo', [GoogleController::class, 'getUserInfo'])->name('google.userinfo');

Route::post('/contents/search', [ContentsController::class, 'search'])->name('contents.search');

Route::get('/contents/{content}', [ContentsController::class, 'show'])->name('contents.show');

Route::get('/last-contents', [ContentsController::class, 'lastContents']);

Route::get('/dashboard-stats',[ContentsController::class, 'dashboardStats']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile/bind/github', [UserController::class, 'bindGitHub']);
    Route::post('/profile/bind/google', [UserController::class, 'bindGoogle']);

    Route::post('/profile/unbind/github', [UserController::class, 'unbindGitHub']);
    Route::post('/profile/unbind/google', [UserController::class, 'unbindGoogle']);

    Route::post('/reviews', [ReviewsController::class, 'store'])->name('reviews.store');
    Route::post('toggleLike/review/{id}', [ReviewsController::class, 'toggleLike'])->name('reviews.toggleLike');
    Route::delete('delete/review/{id}', [ReviewsController::class, 'destroy']);
    Route::post('toggleFavorite/content/{id}', [ContentsController::class, 'toggleFavorite'])->name('contents.toggleFavorite');
    Route::get('lastUserReviews', [ReviewsController::class, 'lastUserReviews'])->name('reviews.lastUserReviews');
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('/cover/{id}', [ContentsController::class, 'uploadCover'])->name('contents.cover');
    Route::delete('/cover/{id}', [ContentsController::class, 'deleteCover']);

    Route::post('upload-avatar', [UserController::class, 'uploadAvatar'])->name('upload.avatar');
    Route::delete('delete-avatar/{id}', [UserController::class, 'deleteAvatar'])->name('delete.avatar');

    Route::put('edit-profile', [UserController::class, 'editProfile'])->name('edit.profile');

    Route::put('profile/password', [UserController::class, 'editPassword'])->name('edit.password');
});

Route::get('/users/{user}/avatar', [UserController::class, 'getAvatar']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('contents', [ContentsController::class, 'store'])->name('contents.store');
    Route::post('contents/{content}', [ContentsController::class, 'update'])->name('contents.update');
    Route::delete('contents/{content}', [ContentsController::class, 'destroy'])->name('contents.destroy');


    Route::delete('delete-review/{review}', [ReviewsController::class, 'destroy'])->name('reviews.destroy');

    Route::post('/contents/{id}/restore', [ContentsController::class, 'restore']);
    Route::delete('/contents/{id}/force', [ContentsController::class, 'forceDelete']);

    Route::post('/reviews/{id}/restore', [ReviewsController::class, 'restore']);
    Route::delete('/reviews/{id}/force', [ReviewsController::class, 'forceDelete']);

    Route::post('update-role/{user}', [UserController::class, 'updateRole'])->name('update.role');
    Route::delete('delete-user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
    Route::delete('/users/{id}/force', [UserController::class, 'forceDelete']);
    Route::post('/users/search', [UserController::class, 'search'])->name('users.search');

    Route::post('/reviews/search', [ReviewsController::class, 'search'])->name('reviews.search');


    Route::get('/admin/contents', [ContentsController::class, 'index'])->name('admin.contents.index');

  


});

Route::post('/contents/search', [ContentsController::class, 'search'])->name('contents.search');

