<?php

namespace App\Models;

class DeliveryRequest extends BaseModel
{
    protected $table = 'delivery_requests';

    public function order()
    {
    	return $this->belongsTo(Order::class, 'order_id');
    }
}