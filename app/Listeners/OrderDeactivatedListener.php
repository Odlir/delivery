<?php

namespace App\Listeners;

use App\Events\OrderDeactivatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repositories\FCM;
use Illuminate\Http\Request;

class OrderDeactivatedListener
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
     * @param  OrderDeactivatedEvent  $event
     * @return void
     */
    public function handle(OrderDeactivatedEvent $event)
    {
        $request = Request::create('api/notifications', 'POST', [
            'order_id' => $event->order->id
        ]);
        
        FCM::sendDeliveryAcceptanceResultToConsumer($request);
    }
}