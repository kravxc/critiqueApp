<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $description
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function role_permissions(): HasMany
    {
        return $this->hasMany(Role_Permission::class);
    }
}


