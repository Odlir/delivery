<?php

namespace App\Repositories;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryRepository
{
    public function create(Request $request)
    {
        $errors = ProductCategory::validate($request,[
            'name' => 'required|string',
            'restaurant_id' => 'integer|exists:restaurants,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $category = new ProductCategory();
        $category->name = $request->name;
        $category->restaurant_id = $request->restaurant_id;
        $category->save();

        return $category;
    }

    public function get(Request $request)
    {
        $errors = ProductCategory::validate($request,[
            'restaurant_id' => 'integer|exists:restaurants,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if($request->has('restaurant_id'))
        {
            return ProductCategory::where('restaurant_id', $request->restaurant_id)->get();
        }

        return ProductCategory::all();
    }

    public function edit(Request $request)
    {
        $errors = ProductCategory::validate($request,[
            'name' => 'required|string'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $category = ProductCategory::find($request->id);

        if($category == null)
            throw new \Exception('Non existent category', 2);

        $category->name = $request->name;
        $category->save();

        return $category;
    }
}