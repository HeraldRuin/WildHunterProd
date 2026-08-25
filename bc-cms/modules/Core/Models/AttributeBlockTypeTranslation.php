<?php

namespace Modules\Core\Models;

use App\BaseModel;

class AttributeBlockTypeTranslation extends BaseModel
{
    protected $table = 'bc_attr_block_types_translations';

    protected $fillable = [
        'name',
    ];
}
