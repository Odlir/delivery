<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\FCM;

class NotificationsController extends Controller
{
	public function notifyNewDeliveryToRestaurant(Request $request)
	{
		return FCM::sendDeliveryAcceptanceToRestaurant($request);
	}

	public function notifyDeliveryAcceptanceResultToConsumer(Request $request)
	{
		return FCM::sendDeliveryAcceptanceResultToConsumer($request);
	}

	public function notifyDeliveryRequestToDeliveryMan(Request $request)
	{
		return FCM::sendDeliveryAcceptanceToDeliveryMan($request);
	}

	public function notifyDeliveryAcceptanceResultToRestaurant(Request $request)
	{
		return FCM::sendDeliveryAcceptanceResultToRestaurant($request);
	}

	public function notifyAdviceToRestaurant(Request $request)
	{
		return FCM::sendDeliveryManAdviceToRestaurant($request);
	}

	public function notifyAdviceToConsumer(Request $request)
	{
		return FCM::sendDeliveryManAdviceToConsumer($request);
	}

	public function notifyOrderDeliveredToConsumer(Request $request)
	{
		return FCM::sendOrderDeliveredAlertToConsumer($request);
	}
}