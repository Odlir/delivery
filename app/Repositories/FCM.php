<?php

namespace App\Repositories;

use App\Models\NotificationToken;
use App\Models\Order;
use App\Models\DeliveryMan;
use App\Models\DeliveryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FCM
{
    private static $CONSUMER_APP_API_KEY = 'AAAAx01rJDE:APA91bHQIFlGFWleceSNZwyuu5mFniALYVDZJTyQ5V3TLgCZitaWo_jfX807D6n38c3YD49O5c3_3mPGPd2XkNosIyha9NZzW66GdD_NCNuPnItsNFGQArSQLlBzndFN0YUFtVUkxhw8ZhVIv-1hk-zxfThNyHigHw';
    // 'AIzaSyBDyTCm2utU93qEVR6wfZIJaiHxeqGNGb4';
    private static $RESTAURANT_APP_API_KEY = 'AAAAOTv4Sio:APA91bGx_dn6hl6kGM6t1dUb4jSdBqPOAgs8YqyEZ6CaP_zaC1nhxpH9WuVZ_ZCTr-Esm01v3tps5MkrjSR9GSfuVSK4FUOBKiVGq_ZwQ_7BJADHEHIz2rxEfycNPU9MalwmQlld9Pwg6QpEzQ8UOxk82_Sh5PvN8w';
    // 'AIzaSyDN3xnAswZeKNRXFjCtbd6OQnQKcnYRPSU';
    private static $DELIVERY_MAN_APP_API_KEY = 'AAAA3HPLpUA:APA91bGBT4QCBF-fUOzfRT7_tCjYhPCJKPMCAhricGdLiL3khmaoYY6NmftQSsmpBemJmQoHcFmRNOYeDxu_d_W7HYfb0a2PEjqTSYCOVk__8lZAELXlSh9GDQDGdUwdvoXKqRFz3TsvRnF_D2BbE43z-Rt4VG2CCg';
    //'AIzaSyDN3xnAswZeKNRXFjCtbd6OQnQKcnYRPSU'

    //consumer -> restaurant
    public static function sendDeliveryAcceptanceToRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consumer_id' => 'required|exists:roles_users,user_id,role_id,2',
            'order_id' => 'required|exists:orders,id,consumer_id,'.$request->consumer_id
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $order = Order::find($request->order_id);

        $notificationToken = NotificationToken::where('restaurant_id', $order->restaurant_id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'new_delivery',
            'order_id' => $order->id,
            'order_date' => $order->created_at,
            'order_total' => $order->total
        ];

        return FCM::sendMessage($notificationToken->token, $data, static::$RESTAURANT_APP_API_KEY, "A tu casa - Restaurante", "Nuevo pedido");
    }

    //restaurant -> delivery_man
    public static function sendDeliveryAcceptanceToDeliveryMan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:roles_users,user_id,role_id,3',
            'delivery_request_id' => 'required|exists:delivery_requests,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $deliveryRequest = DeliveryRequest::find($request->delivery_request_id);

        $deliveryMan = DeliveryMan::where('id', $deliveryRequest->delivery_man_id)->with('user')->first();

        $notificationToken = NotificationToken::where('role_user_role_id', 4)->where('role_user_user_id', $deliveryMan->user->id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'delivery_request',
            'delivery_request_id' => $deliveryRequest->id,
            'order_id' => $deliveryRequest->order_id
        ];

        return FCM::sendMessage($notificationToken->token, $data, static::$DELIVERY_MAN_APP_API_KEY, "A tu casa - Motorizado", "Solicitud de delivery");
    }

    //delivery_man -> restaurant
    public static function sendDeliveryAcceptanceResultToRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_request_id' => 'required|exists:delivery_requests,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $deliveryRequest = DeliveryRequest::find($request->delivery_request_id);

        $order = Order::find($deliveryRequest->order_id);

        $notificationToken = NotificationToken::where('restaurant_id', $order->restaurant_id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'delivery_request_result',
            'order_id' => $deliveryRequest->order_id,
            'status' => $deliveryRequest->request_status,
            'correlative_number' => $order->correlative_number
        ];

        return FCM::sendMessage($notificationToken->token, $data, static::$RESTAURANT_APP_API_KEY, "A tu casa - Motorizado", "Solicitud de delivery");
    }

    //restaurant -> consumer
    public static function sendDeliveryAcceptanceResultToConsumer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $order = Order::find($request->order_id);

        $notificationToken = NotificationToken::where('role_user_role_id', 2)->where('role_user_user_id', $order->consumer_id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'delivery_acceptance_result',
            'order_id' => $order->id,
            'status' => $order->status,
            'estimated_time' => $order->estimated_time,
            'correlative_number' => $order->correlative_number
        ];

        $message = ($order->status == 'accepted'? "aceptado" : "rechazado");

        return FCM::sendMessage($notificationToken->token, $data, static::$CONSUMER_APP_API_KEY, "Confirmación de delivery", "Su pedido fue ".$message);
    }

    //delivery_man -> consumer
    public static function sendDeliveryManAdviceToConsumer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $order = Order::find($request->order_id);

        $notificationToken = NotificationToken::where('role_user_role_id', 2)->where('role_user_user_id', $order->consumer_id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'delivery_man_advice',
            'order_id' => $order->id,
            'order_correlative_number' => $order->correlative_number
        ];

        return FCM::sendMessage($notificationToken->token, $data, static::$CONSUMER_APP_API_KEY, "A tu casa", "Motorizado cerca");
    }

    //delivery_man -> restaurant
    public static function sendDeliveryManAdviceToRestaurant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $order = Order::find($request->order_id);

        $notificationToken = NotificationToken::where('restaurant_id', $order->restaurant_id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'delivery_man_advice',
            'order_id' => $order->id,
            'order_correlative_number' => $order->correlative_number
        ];

        return FCM::sendMessage($notificationToken->token, $data, static::$RESTAURANT_APP_API_KEY, "A tu casa - Restaurante", "Motorizado cerca");
    }

    //delivery_man -> consumer
    public static function sendOrderDeliveredAlertToConsumer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $order = Order::find($request->order_id);

        $notificationToken = NotificationToken::where('role_user_role_id', 2)->where('role_user_user_id', $order->consumer_id)->first();

        if(is_null($notificationToken))
            throw new \Exception(json_encode(['description' => 'Non existent notification-token', 'code' => 2]));

        //buscar order
        $data = [
            'type' => 'delivery_received',
            'order_id' => $order->id,
            'order_correlative_number' => $order->correlative_number
        ];

        return FCM::sendMessage($notificationToken->token, $data, static::$CONSUMER_APP_API_KEY, "A tu casa", "Motorizado cerca");
    }

    private static function sendMessage($receiver, $data, $apiKey, $notificationTitle, $notificationBody, $notificationColor = '#3c763d')
    {
        $fields = array
        (
            'to' => $receiver,
            'data' => $data,
            'notification' => [
                'title' => $notificationTitle,
                'body' => $notificationBody,
                'sound' => 1, 
                'color' => $notificationColor
            ],
            'time_to_live' => 180
        );
        //'Ya perdiste caus@ '.json_decode('"\uD83D\uDE00"'),
        //$parameters['registration_ids'] = $receiver;

        $headers = [
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch );
        curl_close( $ch );

        return $result;
    }
}