<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25/08/2016
 * Time: 11:47 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Request;

class BaseModel extends Model{

    public static $rules = [];

    public static function validate(Request $request, $rulesToApply = null){

        $validator = Validator::make($request->all(),$rulesToApply == null? static::$rules : $rulesToApply);

        if($validator->fails()){
            return $validator->errors();
        }
    }

}