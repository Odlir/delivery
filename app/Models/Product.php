<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'products';
    public $dates = ['deleted_at'];

    public function dependant_products()
    {
    	return $this->hasMany(ProductSubProduct::class, 'product_id', 'id');
    }
}