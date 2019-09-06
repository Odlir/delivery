<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\RoleUser;

class AddressRepository
{
    public function create(Request $request)
    {
        $errors = Address::validate($request, [
            'owner' => 'required|in:consumer,delivery_man,restaurant',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'user_id' => 'required_if:owner,consumer|exists:users,id',
            'restaurant_id' => 'required_if:owner,restaurant|exists:restaurants,id',
            'delivery_man_id' => 'required_if:owner,delivery_man|exists:delivery_men,id',
            'description' => 'string',
            'maps_description' => 'string',
            'additional_details' => 'string'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $address = new Address();
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;

        if($request->has('description'))
            $address->description = $request->description;

        if($request->has('additional_details'))
            $address->additional_details = $request->additional_details;

        if($request->has('maps_description'))
            $address->maps_description = $request->maps_description;

        if($request->owner == 'consumer')
        {
            $roleUser = RoleUser::where('user_id', $request->user_id)
                ->where('role_id', 2)->first();

            if($roleUser == null)
                throw new \Exception('The given user is not a consumer', 2);

            $address->consumer_id = $request->user_id;
        }
        elseif($request->owner == 'delivery_man')
        {
            if(RoleUser::where('user_id', $request->user_id)->where('role_id', 4)->first())
                throw new \Exception('The given user is not a delivery man', 3);

            $address->delivery_man_id = $request->delivery_man_id;
        }
        elseif($request->owner == 'restaurant')
        {
            $address->restaurant_id = $request->restaurant_id;
        }

        $address->save();
        
        return $address;
    }

    public function get(Request $request)
    {
        $errors = Address::validate($request, [
            'consumer_id' => 'exists:roles_users,user_id,role_id,2',
            'delivery_man_id' => 'exists:delivery_men,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if(!is_null($request->id))
        {
            $address = Address::find($request->id);

            if(is_null($address))
                throw new \Exception('Non existent address', 2);

            return $address;
        }

        if($request->has('consumer_id'))
        {
            return Address::where('consumer_id', $request->consumer_id)->get();
        }
        elseif($request->has('delivery_man_id'))
        {
            return Address::where('delivery_man_id', $request->delivery_man_id)->get();
        }
        
        return Address::all();
    }
    
    public function edit(Request $request)
    {
        $errors = Address::validate($request, [
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'description' => 'string',
            'maps_description' => 'string',
            'additional_details' => 'string'
        ]);

        if($errors)
            throw new \Exception(json_encode($errors), 1);

        $address = Address::find($request->id);

        if($address == null)
            throw new \Exception('Non-existent address', 2);
                
        if($request->has('latitude'))
            $address->latitude = $request->latitude;

        if($request->has('longitude'))
            $address->longitude = $request->longitude;

        if($request->has('description'))
            $address->description = $request->description;

        if($request->has('additional_details'))
            $address->additional_details = $request->additional_details;

        if($request->has('maps_description'))
            $address->maps_description = $request->maps_description;

        $address->save();

        return $address;
    }
    
    public function delete(Request $request)
    {
        $errors = Address::validate($request, [
            'user_id' => 'required|exists:users,id'
        ]);

        if($errors)
            throw new \Exception(json_encode($errors), 1);

        $address = Address::find($request->id);

        if($address == null)
            throw new \Exception('Non-existent address', 2);

        $address->delete();

        return $address;
    }
}