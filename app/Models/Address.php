<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends BaseModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'addresses';
}
