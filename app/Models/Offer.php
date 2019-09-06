<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends BaseModel
{
    use SoftDeletes;
    protected $table = 'offers';
    public $dates = ['deleted_at'];
}
