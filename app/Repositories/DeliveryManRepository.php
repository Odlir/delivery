<?php

namespace App\Repositories;

use App\Models\CellPhoneNumber;
use App\Models\Restaurant;
use App\Models\DeliveryMan;
use App\Models\RoleUser;
use App\Models\Order;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryManRepository
{
    public function create(Request $request)
    {
        $errors = DeliveryMan::validate($request, [
            'user_id' => 'required|exists:roles_users,user_id,role_id,4',
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $deliveryMan = new DeliveryMan();
        $deliveryMan->user_id = $request->user_id;
        $deliveryMan->status = 'free';

        $deliveryMan->save();
        
        return $deliveryMan;
    }

    public function get(Request $request)
    {            
        if($request->id != null)
        {
            $deliveryMan = DeliveryMan::with('user')->with('address')
                ->where('delivery_men.id', $request->id)
                ->first();

            if($deliveryMan == null)
                throw new \Exception('Non existent delivery-man', 2);

            $deliveryMan->cellphone = CellPhoneNumber::where('user_id', $deliveryMan->user['id'])->first();

            return $deliveryMan;
        }
        if($request->has('where'))
        {
            if($request->where == 'status')
            {
                return DeliveryMan::with('user')
                    ->where('status', $request->status)
                    ->get();
            }
        }

        return DeliveryMan::join('users', 'users.id', 'delivery_men.user_id')
            ->join('cellphone_numbers', 'cellphone_numbers.user_id', 'users.id')
            ->select('delivery_men.*', 'users.name', 'users.surname', 'users.email', 'users.dni', 'cellphone_numbers.number as cellphone_number')
            ->get();
    }

    public function sendDeliveryRequest(Request $request)
    {        
        return ['success' => false];
    }

    public function edit(Request $request)
    {
        $errors = DeliveryMan::validate($request, [
            'status' => 'in:free,busy,not_available',
            'restaurant_id' => 'integer'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $deliveryMan = DeliveryMan::find($request->id);

        if($deliveryMan == null)
            throw new \Exception('Non existent delivery man', 2);

        if($request->has('status'))
            $deliveryMan->status = $request->status;

        if($request->has('restaurant_id')) {
            if($request->restaurant_id == 0){
                $deliveryMan->restaurant_id = null;
            }
            else if(!is_null(Restaurant::find($request->restaurant_id))){
                $deliveryMan->restaurant_id = $request->restaurant_id;
            }
            else{
                throw new \Exception('Non existent restaurant', 3);
            }
        }

        $deliveryMan->save();

        return $deliveryMan;
    }

    public function assignFreeDeliveryManToOrder(Request $request)
    {
        $deliveryMan = DeliveryMan::where('status', 'free')->first();
        $deliveryMan->status = 'busy';
        $deliveryMan->save();

        $order = Order::find($request->order_id);
        $order->delivery_status = 'at_departure';
        $order->delivery_man_id = $deliveryMan->id;
        $order->save();

        return $deliveryMan;
    }
}