<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use App\Models\Restaurant;
use App\Models\Address;
use Illuminate\Support\Facades\View;

class OrderCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderCreatedEvent  $event
     * @return void
     */
    public function handle(OrderCreatedEvent $event)
    {
        $user = User::find($event->order->consumer_id);
        $restaurant = Restaurant::find($event->order->restaurant_id);
        $destiny = Address::find($event->order->destiny_address_id);

        $messageData = [
            'user_name' => $user->name." ".$user->surname,
            'restaurant' => $restaurant->name,
            'destiny_description' => $destiny->description,
            'destiny_maps_description' => $destiny->maps_description
        ];

        $emailView = View::make('email', $messageData);

        $curl = curl_init();
        $title = urlencode('Nueo pedido');
        $body = urlencode('Su pedido para el restaurante '.$restaurant->name.' se encuentra en proceso.');

        curl_setopt($curl, CURLOPT_URL, 'http://idealo.pe/test.php?title='.$title.'&email='.$user->email.'&message='.$body);
        // 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
        
        $result = curl_exec($curl);
        curl_close($curl);
    }
}