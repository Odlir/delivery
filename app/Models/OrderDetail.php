<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends BaseModel
{
    use SoftDeletes;

    protected $table = 'order_details';
    public $dates = ['deleted_at'];
}
