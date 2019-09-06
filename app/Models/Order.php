<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseModel
{
    use SoftDeletes;

    protected $table = 'orders';
    public $dates = ['deleted_at'];

	public function restaurant()
    {
    	return $this->hasOne(Restaurant::class, 'id', 'restaurant_id');
    }

    public function destinyAddress()
    {
    	return $this->hasOne(Address::class, 'id', 'destiny_address_id');
    }
}