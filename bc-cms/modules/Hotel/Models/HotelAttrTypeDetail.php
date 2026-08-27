<?php

namespace Modules\Hotel\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\AttributeBlockType;

class HotelAttrTypeDetail extends Model
{
    protected $table = 'bc_hotel_attr_type_details';

    protected $fillable = [
        'hotel_id',
        'block_type_id',
        'details',
        'create_user',
        'update_user',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function blockType()
    {
        return $this->belongsTo(AttributeBlockType::class, 'block_type_id', 'id');
    }
}
