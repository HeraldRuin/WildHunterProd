<?php

namespace Modules\Core\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeBlock extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bc_attr_blocks';

    protected $fillable = [
        'name',
        'service',
    ];

    public function types()
    {
        return $this->hasMany(AttributeBlockType::class, 'block_id', 'id')->orderBy('position')->orderBy('id');
    }
}
