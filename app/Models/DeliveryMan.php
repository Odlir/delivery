<?php

namespace App\Models;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryMan extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'delivery_men';
    public $dates = ['delete_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'delivery_man_id');
    }
}