<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role_id
 * @property string $bio
 * @property string $avatar
 * @property array|string|null $preferences
 * @property string $github_id
 * @property string $github_token
 * @property string $github_refresh_token
 * @property string $google_id
 * @property string $google_token
 * @property string $google_refresh_token
 */
class User extends Authenticatable
{

    use HasApiTokens, Notifiable, HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'bio',
        'avatar',
        'preferences',
        'github_id',
        'github_token',
        'github_refresh_token',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'deleted_at' => 'datetime'
        ];
    }
    protected $appends = ['avatar_url'];
    public function role(): BelongsTo
    {
    return $this->belongsTo(Role::class);
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    public function getAvatarUrlAttribute(): string|null
    {
        if (!$this->avatar) {
            return null;
        }
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }
        return Storage::disk('yandex-s3')->url($this->avatar);
    }
    public function setAvatarAttribute($value)
    {
        if ($value instanceof UploadedFile) {
            $filename = 'avatars/' . $this->id . '/' . uniqid() . '.' . $value->getClientOriginalExtension();

            Storage::disk('yandex-s3')->put($filename, file_get_contents($value));

            $this->attributes['avatar'] = $filename;
        } else {
            $this->attributes['avatar'] = $value;
        }
    }
    public function deleteAvatar(){
        if ($this->avatar && Storage::disk('yandex-s3')->exists($this->avatar)) {
            Storage::disk('yandex-s3')->delete($this->avatar);
            $this->avatar = null;
            return $this->save();
        }
        return false;
    }
    public function addToFavorites(int $id):void
    {
        $preferences = $this->preferences ?? [];
        $favorites = $preferences['favorites'] ?? [];

        if(!in_array($id, $favorites)){
            $favorites[] = $id;
            $preferences['favorites'] = $favorites;
            $this->update(['preferences' => $preferences]);
        }
    }
    public function removeFromFavorites(int $id): void
    {
        $preferences = $this->preferences ?? [];
        $favorites = $preferences['favorites'] ?? [];

        $key = array_search($id, $favorites);
        if($key !== false){
            unset($favorites[$key]);
            $preferences['favorites'] = array_values($favorites);
            $this->update(['preferences' => $preferences]);
        }
    }
    public function hasFavorited(int $id): bool
    {
        $preferences = $this->preferences ?? [];
        $favorites = $preferences['favorites'] ?? [];
        return in_array($id, $favorites);
//        return in_array($id, $this->favorites);
    }

}
