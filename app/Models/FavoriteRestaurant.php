<?php

namespace App\Models;

use App\Models\Restaurant;

class FavoriteRestaurant extends BaseModel
{
    protected $table = 'favorite_restaurants';
    public $timestamps = false;

    public function restaurant()
    {
    	return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }
}
