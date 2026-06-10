<?php
namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends \App\User
{
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class,'role_id');
    }
}
