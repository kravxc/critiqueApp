<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $role_id
 * @property string $permission_id
 */
class Role_Permission extends Model
{
    protected $fillable = [
        'role_id',
        'permission_id',
    ];
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
