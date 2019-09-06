<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Offer;
use App\Models\ProductSubProduct;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function create(Request $request)
    {
        $errors = Product::validate($request, [
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'product_category_id' => 'integer|exists:product_categories,id',
            'is_independent' => 'required|boolean',
            'sub_products' => 'required_if:is_independent,false|array',
            'sub_products.*.id' => 'exists:products,id',
            'description' => 'string'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->restaurant_id = $request->restaurant_id;
        $product->is_independent = $request->is_independent;
        $product->description = $request->description;

        if($request->has('product_category_id'))
            $product->product_category_id = $request->product_category_id;

        $product->save();

        if(!is_null($request->sub_products) && count($request->sub_products) > 0)
        {
            $sub_products = [];

            for($i = 0; $i < count($request->sub_products); ++$i)
            {
                $subProduct = new ProductSubProduct();
                $subProduct->product_id = $product->id;
                $subProduct->sub_product_id = $request->sub_products[$i];
                $subProduct->save();

                $sub_products[] = $subProduct;
            }

            $product->sub_products = $sub_products;
        }

        return $product;
    }
    
    public function get(Request $request)
    {
        $errors = Product::validate($request, [
            'restaurant_id' => 'exists:restaurants,id',
            'only_available' => 'boolean'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if($request->id != null)
        {
            //$product = Product::find($request->id);
            $product = Product::where('id', $request->id)
                ->with('dependant_products')
                ->first();

            if($product == null)
                throw new \Exception('Non existent product', 2);
            
            /*
            $product->dependant_products = Product::where('products.id', $product->id)
                ->join('products_sub_products', 'products_sub_products.product_id', 'products.id')
                ->select('products.name', 'products.id')
                ->get();
            */

            return $product;
        }

        if(!$request->has('only_available'))
            $request->only_available = false;

        if($request->has('restaurant_id'))
        {
            if($request->only_available)
            {
                return Product::where('products.restaurant_id', $request->restaurant_id)
                    ->where('products.available', true)
                    ->leftJoin('resources', 'resources.product_id', 'products.id')
                    ->leftJoin('product_categories', 'product_categories.id', 'products.product_category_id')
                    ->with('dependant_products')
                    ->select('products.*', 'resources.path', 'product_categories.name as category')
                    ->get();
            }
            else
            {
                return Product::where('products.restaurant_id', $request->restaurant_id)
                    ->leftJoin('resources', 'resources.product_id', 'products.id')
                    ->leftJoin('product_categories', 'product_categories.id', 'products.product_category_id')
                    ->with('dependant_products')
                    ->select('products.*', 'resources.path', 'product_categories.name as category')
                    ->get();
            }
        }

        return Product::all();
    }

    public function createOffer(Request $request)
    {
        $errors = Offer::validate($request, [
            'product_id' => 'required|exists:products,id',
            'begin_date' => 'required|date',
            'end_date' => 'required|date',
            'discount' => 'required|numeric'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $offer = new Offer();
        $offer->begin_date = $request->begin_date;
        $offer->end_date = $request->end_date;
        $offer->discount = $request->discount;
        $offer->product_id = $request->product_id;

        $offer->save();

        return $offer;
    }

    public function edit(Request $request)
    {
        $errors = Product::validate($request, [
            'price' => 'numeric',
            'name' => 'string',
            'product_category_id' => 'integer|exists:product_categories,id',
            'available' => 'boolean',
            'description' => 'string',
            'is_independent' => 'boolean',
            'sub_products' => 'array',
            'sub_products.*.sub_product_id' => 'exists:products,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $product = Product::find($request->id);

        if($product == null)
            throw new \Exception('Non existent product', 2);

        if($request->has('price'))
            $product->price = $request->price;

        if($request->has('name'))
            $product->name = $request->name;

        if($request->has('product_category_id'))
            $product->product_category_id = $request->product_category_id;

        if($request->has('available'))
            $product->available = $request->available;

        if($request->has('description'))
            $product->description = $request->description;

        $subProducts = [];

        if($request->has('is_independent'))
        {
            $product->is_independent = $request->is_independent;
            
            DB::table('products_sub_products')->where('product_id', $product->id)->delete();
            //return ProductSubProduct::where('product_id', $product->id)->delete();

            if($product->is_independent){

                for($s = 0; $s < count($request->sub_products); ++$s){
                    $subProduct = new ProductSubProduct();
                    $subProduct->product_id = $product->id;
                    $subProduct->sub_product_id = $request->sub_products[$s]['sub_product_id'];
                    $subProduct->save();

                    $subProducts[] = $subProduct;
                }
            }
        }

        $product->save();

        $product->sub_products = $subProducts;

        return $product;
    }
    
    public function delete(Request $request)
    {
        $product = Product::find($request->id);

        if($product == null)
            throw new \Exception('Non existent product', 1);
        
        $product->delete();

        return $product;
    }
}