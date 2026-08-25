<?php

namespace Modules\Core\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeBlockType extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bc_attr_block_types';

    protected $fillable = [
        'name',
        'block_id',
        'service',
        'position',
    ];

    public function block()
    {
        return $this->belongsTo(AttributeBlock::class, 'block_id', 'id');
    }

    public function attributes()
    {
        return $this->hasMany(Attributes::class, 'block_type_id', 'id')->orderBy('position');
    }
}
