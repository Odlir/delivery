<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends BaseModel
{
    use SoftDeletes;

    protected $table = 'restaurants';
    public $dates = ['deleted_at'];

    public function address()
    {
        return $this->hasOne(Address::class, 'restaurant_id');
    }

    public function deliveryZone()
    {
        return $this->hasOne(DeliveryZone::class, 'restaurant_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function rating_summary()
    {
        return $this->hasOne(RestaurantRatingSummary::class, 'restaurant_id');
    }
}