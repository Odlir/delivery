<?php

namespace App\Models;

class DeliveryZone extends BaseModel
{
    protected $table = 'delivery_zones';

    protected $hidden = [
        'zone_polygon'
    ];
}
