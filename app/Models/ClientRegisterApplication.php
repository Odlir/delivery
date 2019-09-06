<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ClientRegisterApplication extends BaseModel
{
    use SoftDeletes;

    protected $table = 'client_register_applications';
    protected $dates = ['deleted_at'];
}
