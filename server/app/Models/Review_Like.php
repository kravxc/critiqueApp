<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $reviews_id
 * @property string $users_id
 * @property string $type
 */
class Review_Like extends Model
{
    protected $table = 'review_likes';

    protected $fillable = [
      'reviews_id',
      'user_id',
      'type',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
