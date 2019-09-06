<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Address;
use App\Models\Restaurant;
use App\Models\DeliveryMan;
use App\Models\DeliveryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryRequestRepository
{
	public function create(Request $request)
	{
		$errors = DeliveryRequest::validate($request, [
			'order_id' => 'required|exists:orders,id',
			'private' => 'required|boolean'
		]);

		//throw new \Exception( json_encode($request->all()), 1);
		
		if(!is_null($errors))
			throw new \Exception(json_encode($errors), 1);

		$order = Order::find($request->order_id);

		if(!is_null($order->delivery_man_id))
			throw new \Exception('A delivery man is attending the order', 3);
		
		$lastRequests = DeliveryRequest::where('order_id', $request->order_id)->orderBy('id', 'desc')->get();

		//return $lastRequests;

		$employeesIds = '0';

		if(count($lastRequests) > 0)
		{
			$last = DeliveryRequest::find($lastRequests[0]['id']);
			$last->active = false;
			$last->save();

			foreach ($lastRequests as $deliveryRequest) 
			{
				$employeesIds .= ','.$deliveryRequest['delivery_man_id'];
			}
		}
		
		

		//$restaurantAddress = Address::find($order->origin_address_id);

		$deliveryMen = null;

		if($request->private){
			$deliveryMen = DB::select('select dm.* from delivery_men dm
	            where dm.restaurant_id = '.$order->restaurant_id.' and dm.id not in ('.$employeesIds.') and dm.status != "not_available" ');
				//and haversine(a.latitude, a.longitude,'.$restaurantAddress->latitude.','.$restaurantAddress->longitude.') < 3000');
			/*
			$deliveryMen = DB::select('select dm.* from delivery_men dm, addresses a 
	            where dm.restaurant_id = '.$order->restaurant_id.' and dm.id not in ('.$employeesIds.') and a.delivery_man_id = dm.id and dm.status != "not_available" ');
				//and haversine(a.latitude, a.longitude,'.$restaurantAddress->latitude.','.$restaurantAddress->longitude.') < 3000');
			*/
		}
		else{
			/*
			$deliveryMen = DB::select('select dm.* from delivery_men dm, addresses a 
	            where dm.id not in ('.$employeesIds.') and a.delivery_man_id = dm.id and dm.status != "not_available" ');
				//and haversine(a.latitude, a.longitude,'.$restaurantAddress->latitude.','.$restaurantAddress->longitude.') < 3000');
			*/
			$deliveryMen = DB::select('select dm.* from delivery_men dm
	            where dm.restaurant_id is null and dm.id not in ('.$employeesIds.') and dm.status != "not_available"');
				//and haversine(a.latitude, a.longitude,'.$restaurantAddress->latitude.','.$restaurantAddress->longitude.') < 3000');
		}

		if(count($deliveryMen) == 0)
			throw new \Exception('There are no more delivery_men availables', 2);			

		$deliveryMan = $deliveryMen[0];
            
        $deliveryRequest = new DeliveryRequest();
        $deliveryRequest->order_id = $request->order_id;
        $deliveryRequest->delivery_man_id = $deliveryMan->id;
        $deliveryRequest->private = $request->private;
        $deliveryRequest->save();

        return $deliveryRequest;
	}

	public function edit(Request $request)
	{
		$request['id'] = $request->id;

		$errors = DeliveryRequest::validate($request, [
			'delivery_man_id' => 'required|exists:delivery_men,id',
			'id' => 'required|exists:delivery_requests,id,delivery_man_id,'.$request->delivery_man_id,
			'accepted' => 'required_without:delivery_status|boolean',
			'delivery_status' => 'required_without:accepted|in:not_started,underway,delivered'
		]);

		if(!is_null($errors))
			throw new \Exception(json_encode($errors), 1);
		
		$deliveryRequest = DeliveryRequest::find($request->id);

		if($request->has('accepted'))
		{
			if($request->accepted)
			{
				$deliveryRequest->request_status = 'accepted';
				$deliveryRequest->delivery_status = 'not_started';

				$order = Order::find($deliveryRequest->order_id);
				$order->delivery_man_id = $deliveryRequest->delivery_man_id;
				$order->save();

				$deliveryMan = DeliveryMan::find($deliveryRequest->delivery_man_id);
				$deliveryMan->status = 'busy';
				$deliveryMan->save();
			}
			else
			{
				$deliveryRequest->request_status = 'denied';
				$deliveryRequest->active = false;
			}
		}

		if($request->has('delivery_status'))
		{
			$deliveryRequest->delivery_status = $request->delivery_status;

			if($request->delivery_status == 'delivered')
			{
				$deliveryRequest->active = false;

				if(count(DB::select('select * from delivery_requests where delivery_man_id = '.$deliveryRequest->delivery_man_id.' and active = true order by id desc limit 2')) == 1)
				{
					$deliveryMan = DeliveryMan::find($deliveryRequest->delivery_man_id);
					$deliveryMan->status = 'free';
					$deliveryMan->save();
				}

				$order = Order::find($deliveryRequest->order_id);
				$order->active = false;
				$order->save();
			}
		}

		$deliveryRequest->save();		

		return $deliveryRequest;			
	}

	public function get(Request $request)
	{
		$errors = DeliveryRequest::validate($request, [
			'delivery_man_id' => 'exists:delivery_requests,delivery_man_id,active,true'
		]);

		$deliveryRequests = DeliveryRequest::where('delivery_man_id', $request->delivery_man_id)
			->where('active', true)->with('order')->get();

		$currentDate = Carbon::now();

		$currentDeliveryRequests = [];

		foreach($deliveryRequests as $deliveryRequest)
		{
			if($currentDate->diffInMinutes(new Carbon($deliveryRequest->created_at)) > 3)
			{
				$deliveryRequest->active = false;
				$deliveryRequest->save();

				continue;
			}

			$deliveryRequest->order->restaurant = Restaurant::find($deliveryRequest->order->restaurant_id);

			$currentDeliveryRequests[] = $deliveryRequest;
		}

		return $currentDeliveryRequests;
	}
}