<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $content_id
 * @property string $user_id
 * @property string $title
 * @property string $text
 */
class Review extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'content_id',
        'user_id',
        'title',
        'text'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Review_Like::class, 'reviews_id');
    }

    // Отношение для обычных лайков
    public function regularLikes(): HasMany
    {
        return $this->hasMany(Review_Like::class, 'reviews_id')->where('type', 'like');
    }

    // Отношение для авторских лайков
    public function authorLikes(): HasMany
    {
        return $this->hasMany(Review_Like::class, 'reviews_id')->where('type', 'author_like');
    }

    // Подсчет обычных лайков
    public function getRegularLikesCountAttribute(): int
    {
        return $this->regularLikes()->count();
    }

    // Подсчет авторских лайков
    public function getAuthorLikesCountAttribute(): int
    {
        return $this->authorLikes()->count();
    }

    // Общее количество лайков
    public function getTotalLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }
}
