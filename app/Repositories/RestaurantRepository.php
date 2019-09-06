<?php

namespace App\Repositories;

use App\Models\DeliveryZone;
use App\Models\Restaurant;
use App\User;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Product;
use App\Models\FavoriteRestaurant;
use Illuminate\Support\Facades\DB;
use App\Models\RestaurantRatingSummary;

class RestaurantRepository
{
    public function create(Request $request)
    {
        $errors = Restaurant::validate($request, [
            'client_id' => 'required|exists:roles_users,user_id,role_id,3',
            'name' => 'required|string',
            'shipping_charge' => 'required|numeric',
            'minimum_delivery_value' => 'numeric',
            'security_code' => 'required|digits:4',
            'invoice_enabled' => 'boolean',
            'card_payment_enabled' => 'boolean',
            'short_description' => 'string',
            'large_description' => 'string'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $restaurant = new Restaurant();
        $restaurant->name = $request->name;
        $restaurant->shipping_charge = $request->shipping_charge;
        $restaurant->client_id = $request->client_id;
        $restaurant->active = false;
        $restaurant->service_enabled = false;
        $restaurant->security_code = $request->security_code;

        if($request->has('invoice_enabled'))
            $restaurant->invoice_enabled = $request->invoice_enabled;

        if($request->has('card_payment_enabled'))
            $restaurant->card_payment_enabled = $request->card_payment_enabled;

        if($request->has('minimum_delivery_value'))
            $restaurant->minimum_delivery_value = $request->minimum_delivery_value;

        if($request->has('short_description'))
            $restaurant->short_description = $request->short_description;

        if($request->has('large_description'))
            $restaurant->large_description = $request->large_description;
        
        $restaurant->save();

        return $restaurant;
    }

    public function edit(Request $request)
    {
        $errors = Restaurant::validate($request, [
            'name' => 'string',
            'shipping_charge' => 'numeric',
            'minimum_delivery_value' => 'numeric',
            'security_code' => 'digits:4',
            'service_enabled' => 'boolean',
            'invoice_enabled' => 'boolean',
            'card_payment_enabled' => 'boolean',
            'active' => 'boolean',
            'short_description' => 'string',
            'large_description' => 'string'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $restaurant = Restaurant::find($request->id);

        if($restaurant == null)
            throw new \Exception('Non existent restaurant', 2);
            
        if($request->has('name'))
            $restaurant->name = $request->name;

        if($request->has('shipping_charge'))
            $restaurant->shipping_charge = $request->shipping_charge;

        if($request->has('minimum_delivery_value'))
            $restaurant->minimum_delivery_value = $request->minimum_delivery_value;

        if($request->has('security_code'))
            $restaurant->security_code = $request->security_code;

        if($request->has('active'))
            $restaurant->active = $request->active;

        if($request->has('service_enabled'))
            $restaurant->service_enabled = $request->service_enabled;

        if($request->has('invoice_enabled'))
            $restaurant->invoice_enabled = $request->invoice_enabled;

        if($request->has('card_payment_enabled'))
            $restaurant->card_payment_enabled = $request->card_payment_enabled;

        if($request->has('short_description'))
            $restaurant->short_description = $request->short_description;

        if($request->has('large_description'))
            $restaurant->large_description = $request->large_description;

        $restaurant->save();

        return $restaurant;
    }

    //http://stackoverflow.com/questions/9409195/how-to-get-complete-address-from-latitude-and-longitude
    public function get(Request $request)
    {
        $errors = Restaurant::validate($request, [
            'where' => 'in:client,district,zone_contains_point,within_circle',
            'client_id' => 'required_if:where,client|exists:roles_users,user_id,role_id,3',
            'district_id' => 'required_if:where,district|exists:districts,id',
            'point' => 'required_if:where,zone_contains_point,within_circle',
            'point.latitude'=> 'numeric',
            'point.longitude'=> 'numeric',
            'radius' => 'required_if:where,within_circle|numeric'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if($request->id != null)
        {
            $restaurant = Restaurant::where('restaurants.id', $request->id)
                ->leftJoin('resources', 'resources.restaurant_id', 'restaurants.id')
                ->orderBy('resources.id', 'desc')
                ->select('restaurants.*', 'resources.path as path')
                ->first();

            $restaurant->address = Address::where('restaurant_id', $request->id)->first();
            $restaurant->client = User::where('id', $restaurant->client_id)->first();
            $restaurant->delivery_zone = DeliveryZone::where('restaurant_id', $restaurant->id)
                ->select('delivery_zones.id', 'delivery_zones.zone_polygon_as_text')
                ->first();
            
            return $restaurant;
        }
        elseif ($request->has('where'))
        {
            if($request->where == 'client')
            {
                return Restaurant::where('client_id', $request->client_id)->get();
            }
            if($request->where == 'district')
            {
                return Restaurant::join('addresses', 'restaurant_id', '=', 'restaurant.id')
                    ->where('addresses.district_id', $request->district_id)
                    ->where('restaurants.active', true)
                    ->get();
            }
            elseif($request->where == 'zone_contains_point')
            {
                $restaurants = DB::select('select r.*, res.path from restaurants r left join resources res on res.restaurant_id = r.id, delivery_zones dz where r.active = true and r.id = dz.restaurant_id and 
                 ST_CONTAINS(dz.zone_polygon, point('.$request->point['latitude'].','.$request->point['longitude'].'))');
                
                return $restaurants;
            }
            elseif($request->where == 'within_circle')
            {
                $center = $request->point;

                $restaurants = DB::select('select r.*, res.path from restaurants r left join resources res on res.restaurant_id = r.id, addresses a 
                    where r.active = true and r.id = a.restaurant_id');

//                    and haversine('.$center['latitude'].', '.$center['longitude'].', a.latitude, a.longitude) <= '.$request->radius);
                
                foreach ($restaurants as $restaurant)
                {
                    $restaurant->summary = RestaurantRatingSummary::where('restaurant_id', $restaurant->id)->first();
                }

                return $restaurants;
            }
        }
        
        return Restaurant::with('client')->with('rating_summary')->get();
    }
        
}

/*
DELIMITER $$
CREATE function haversine(lat1 double, lon1 double, lat2 double, lon2 double) returns double
BEGIN

declare dla double;
declare dlo double;
declare a double;
declare c double;

set dla = sin(((lat2 - lat1) * pi()/180) / 2);
set dlo = sin(((lon2 - lon1) * pi()/180) / 2);
set a = dla * dla + cos(lat1 * pi() / 180) * cos(lat2 * pi() / 180) * dlo * dlo;
set c = 2 * atan2(sqrt(a), sqrt(1 - a));

return 1000 * 6371 * c;

END$$

*/