<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Restaurant;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\DeliveryMan;
use App\Models\DeliveryRequest;
use App\Models\PaymentMode;
use App\Models\VoucherType;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\OrderCreatedEvent;
use App\Models\CellPhoneNumber;
use App\Events\OrderDeactivatedEvent;

class OrderRepository
{
    public function create(Request $request)
    {
        $errors = Order::validate($request, [
            'restaurant_id' => 'required|exists:restaurants,id',
            'consumer_id' => 'required|exists:roles_users,user_id,role_id,2',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'received_amount' => 'required|numeric',
            'details' => 'required|array',
            'details.*.amount' => 'required|integer',
            'details.*.product_id' => 'required|exists:products,id,restaurant_id,'.$request->restaurant_id,
            'details.*.main_product_id' => 'exists:products,id,restaurant_id,'.$request->restaurant_id,
            'voucher_type_id' => 'required|exists:voucher_types,id',
            'client_name' => 'string|required_if:voucher_type_id,1',
            'corporate_name' => 'string|required_if:voucher_type_id,2',
            'ruc' => [
                'required_if:voucher_type_id,2',
                'regex:#^10|^20#',
                'digits:11'
            ],
            'destiny_address_id' => 'required_without:address|exists:addresses,id',
            'address' => 'required_without:destiny_address_id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $originAddress = Address::where('restaurant_id', $request->restaurant_id)->first();

        $order = new Order();
        $order->total_without_delivery = 0;
        $order->total = 0;
        $order->status = 'waiting';
        $order->restaurant_id = $request->restaurant_id;
        $order->delivery_charge = Restaurant::find($request->restaurant_id)->shipping_charge;
        $order->consumer_id = $request->consumer_id;
        $order->payment_mode_id = $request->payment_mode_id;
        $order->voucher_type_id = $request->voucher_type_id;
        $order->origin_address_id = $originAddress->id;
        $order->received_amount = $request->received_amount;
        $order->estimated_time = 0;
        $order->returned_amount = 0;
        $order->distance = 4;
        $order->correlative_number = (DB::select('select count(id) as order_number from orders where restaurant_id = '.$request->restaurant_id)[0]->order_number + 1);
        $order->serial_number = $request->restaurant_id.'-'.$order->correlative_number;

        if($request->voucher_type_id == 1)
        {
            $order->client_name = $request->client_name;
        }
        elseif($request->voucher_type_id == 2)
        {
            $order->ruc = $request->ruc;
            $order->corporate_name = $request->corporate_name;
        }

        $details = [];

        foreach ($request->details as $detail)
        {
            $product = Product::find($detail['product_id']);

            $orderDetail = new OrderDetail();
            $orderDetail->amount = $detail['amount'];
            $orderDetail->subtotal = $detail['amount'] * $product->price;            
            $orderDetail->product_id = $product->id;

            $order->total_without_delivery += $orderDetail->subtotal;

            if(isset($detail['main_product_id']))
                $orderDetail->main_product_id = $detail['main_product_id'];

            $details[] = $orderDetail;
        }

        $order->total = $order->delivery_charge + $order->total_without_delivery;
        $order->returned_amount = $order->received_amount - $order->total;

        if($order->returned_amount < 0)
            throw new \Exception('The received_amount is lower than total', 2);

        if($request->has('address'))
        {
            $addressRequest = Request::create('api/addresses', 'POST', $request->address);

            $addressRepository = new AddressRepository();
            $address = $addressRepository->create($addressRequest);

            $order->destiny_address_id = $address->id;
        }
        else
        {
            $order->destiny_address_id = $request->destiny_address_id;
        }

        $order->save();

        foreach($details as $detail)
        {
            $detail->order_id = $order->id;
            $detail->save();
        }

        $order->details = $details;

        event(new OrderCreatedEvent($order));

        return $order;
    }

    public function edit(Request $request)
    {
        $errors = Order::validate($request, [
            'estimated_time' => 'numeric',
            'delivery_man_id' => 'exists:delivery_men,id',
            'status' => 'in:accepted,denied',
            'accept_delivery_service' => 'boolean'
        ]);
        
        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $order = Order::find($request->id);

        if(!$order->active)
            throw new \Exception('The order is inactive', 2);

        if($order == null)
            throw new \Exception('Non existent order', 3);

        if($request->has('estimated_time'))
            $order->estimated_time = $request->estimated_time;
        
        if($request->has('status')){
            $order->status = $request->status;

            if($request->status == 'denied')
                $order->active = false;
        }
        
        if($request->has('accept_delivery_service'))
            $order->accept_delivery_service = $request->accept_delivery_service;

        $order->save();

        return $order;
    }

    public function get(Request $request)
    {
        $errors = Order::validate($request, [
            'where' => 'in:restaurant,consumer,delivery_man',
            'restaurant_id' => 'required_if:where,restaurant|exists:restaurants,id',
            'consumer_id' => 'required_if:where,consumer|exists:roles_users,user_id,role_id,2',
            'delivery_man_id' => 'required_if:where,delivery_man|exists:delivery_men,id',
            'include_description' => 'boolean'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if ($request->id != null)
        {
            $order = Order::find($request->id);
            
            if($order == null)
                throw new \Exception('Non existent order', 2);
            
            $order->consumer = User::find($order->consumer_id);

            $order->details = OrderDetail::where('order_id', $order->id)
                    ->join('products', 'products.id', 'order_details.product_id')
                    ->select('order_details.*', 'products.name as product_name', 'products.price as product_price')
                    ->get();

            $order->destiny_address = Address::find($order->destiny_address_id);

            $order->delivery_man = DeliveryMan::join("users", "users.id", "user_id")
                    ->where("delivery_men.id", $order->delivery_man_id)
                    ->select('delivery_men.id as delivery_man_id', 'users.name', 'users.surname')
                    ->first();

            $order->restaurant = Restaurant::where('restaurants.id', $order->restaurant_id)
                ->leftJoin('resources', 'resources.restaurant_id', 'restaurants.id')
                ->select('restaurants.*', 'resources.path as path')
                ->first();
            $order->payment_mode = PaymentMode::find($order->payment_mode_id);
            $order->voucher_type = VoucherType::find($order->voucher_type_id);
            $order->delivery_request = DeliveryRequest::where('order_id', $order->id)->where('active', true)->first();

            $cellphone = CellPhoneNumber::where('user_id', $order->consumer_id)->orderBy('id', 'desc')->first();

            if(!is_null($cellphone)){
                $order->consumer_cellphone = $cellphone->number;
            }

            return $order;
        }
        elseif($request->has('where'))
        {
            if($request->where == 'restaurant')
            {
                if(!$request->has('include_description'))
                    $request->extract_inactive = false;

                if($request->include_description)
                {
                    $orders = Order::with('destinyAddress')
                            ->where('orders.restaurant_id', $request->restaurant_id)
                            ->where('orders.status', '!=', 'denied')
                            ->orderBy('orders.id', 'desc')
                            ->get();
                }
                else
                {
                    $orders = Order::where('restaurant_id', $request->restaurant_id)
                        ->where('status', '!=', 'denied')
                        ->orderBy('id', 'desc')
                        ->get();
                }

                $restaurantOrders = [];

                $currentDate = Carbon::now();

                foreach ($orders as $order)
                {
                    if($order->status == 'waiting' && $currentDate->diffInMinutes(new Carbon($order->created_at)) >= 1)
                    {
                        $order->active = false;
                        $order->status = 'denied';
                        $order->save();

                        event(new OrderDeactivatedEvent($order));

                        continue;
                    }

                    $order->delivery_request = null;

                    $deliveryRequest = DeliveryRequest::where('order_id', $order->id)->where('active', true)->orderBy('id', 'desc')->first();

                    if(!is_null($deliveryRequest))
                    {
                        if($currentDate->diffInMinutes(new Carbon($deliveryRequest->created_at)) > 3)
                        {
                            $deliveryRequest->active = false;
                            $deliveryRequest->save();
                        }

                        if($deliveryRequest->active)
                        {
                            $order->delivery_request = $deliveryRequest;
                            $order->delivery_request->seconds = $currentDate->diffInSeconds(new Carbon($deliveryRequest->created_at));
                        }
                    }

                    $restaurantOrders[] = $order;
                }

                return $restaurantOrders;
            }
            elseif($request->where == 'consumer')
            {
                if($request->has('with'))
                {
                    if($request->with == 'short_description')
                    {
                        $orders = Order::with('destinyAddress')
                            ->where('orders.consumer_id', $request->consumer_id)
                            ->orderBy('orders.id', 'desc')
                            ->get();
                    }
                }
                else
                {
                    $orders = Order::where('consumer_id', $request->consumer_id)->orderBy('id', 'desc')->get();
                }
                
                return $orders;
            }
            elseif($request->where == 'delivery_man')
            {
                return Order::where('delivery_man_id', $request->delivery_man_id)->orderBy('id', 'desc')->get();
            }
        }

        return Order::all();
    }
}