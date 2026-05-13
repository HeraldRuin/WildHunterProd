<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalAvailability extends Model
{
    protected $table = 'bc_animal_availabilities';

    protected $fillable = [
        'animal_id',
        'start_date',
        'end_date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
