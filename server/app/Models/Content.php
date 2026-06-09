<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $title
 * @property string $music_type
 * @property string $artist
 * @property string $genre
 * @property string $release_date
 * @property string $cover_image
 * @property string $label
 * @property string $favorites_count
 * @property string $reviews_count
 * @property string $added_by
 */
class Content extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'music_type',
        'artist',
        'genre',
        'release_date',
        'cover_image',
        'label',
        'favorites_count',
        'reviews_count',
        'added_by',
    ];
    protected $appends = ['cover_image_url'];


    protected $casts = [
        'deleted_at' => 'datetime',
    ];
    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }

        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }

        $baseUrl = 'https://storage.yandexcloud.net/critiqueassets/';
        return $baseUrl . ltrim($this->cover_image, '/');
    }
    public function setCoverImageAttribute($image)
    {
        if ($image instanceof UploadedFile) {
            $filename = 'covers/' . $this->id . '/' . uniqid() . '.' . $image->getClientOriginalExtension();
            Storage::disk('yandex-s3')->put($filename, file_get_contents($image));
            $this->attributes['cover_image'] = $filename;
        }else{
            $this->attributes['cover_image'] = $image;
        }
    }
    public function deleteCoverImage()
    {
        if($this->cover_image && Storage::disk('yandex-s3')->exists($this->cover_image)){
            Storage::disk('yandex-s3')->delete($this->cover_image);
            $this->cover_image = null;
            return $this->save();
        }
        return false;
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
