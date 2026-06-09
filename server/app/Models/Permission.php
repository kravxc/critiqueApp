<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $description
 */
class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    function role_permissions(): HasMany
    {
        return $this->hasMany(Role_Permission::class);
    }
}
