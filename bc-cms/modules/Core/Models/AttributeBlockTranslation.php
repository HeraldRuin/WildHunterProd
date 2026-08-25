<?php

namespace Modules\Core\Models;

use App\BaseModel;

class AttributeBlockTranslation extends BaseModel
{
    protected $table = 'bc_attr_blocks_translations';

    protected $fillable = [
        'name',
    ];
}
