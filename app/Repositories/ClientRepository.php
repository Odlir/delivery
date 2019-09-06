<?php

namespace App\Repositories;

use App\Models\Restaurant;
use App\User;
use Illuminate\Http\Request;

class ClientRepository
{ 
    public function get(Request $request)
    {
        $client = User::join('roles_users', 'roles_users.user_id','users.id')
            ->join('cellphone_numbers', 'cellphone_numbers.user_id', 'users.id')
            ->where('users.id', $request->id)
            ->select('users.*', 'cellphone_numbers.number as cellphone_number')
            ->first();

        if($client == null)
            throw new \Exception('Non-existent client', 1);

        if($request->has('with'))
        {
            if($request->with == 'restaurants')
            {

                $client->restaurants = Restaurant::where('client_id', $client->id)
                    ->with('address')->with('deliveryZone')
                    ->get();
                //dd($client);
            }
        }
        
        return $client;
    }

    public function all(Request $request)
    {
        $clients = User::join('roles_users', 'roles_users.user_id','users.id')
            ->leftJoin('cellphone_numbers', 'cellphone_numbers.user_id', 'users.id')
            ->where('roles_users.role_id', 3)
            ->select('users.*','roles_users.role_id', 'cellphone_numbers.id as cellphone_id', 'cellphone_numbers.number as cellphone_number')
            ->get();

        return $clients;
    }
}