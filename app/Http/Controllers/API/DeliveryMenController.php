<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\DeliveryManRepository;

class DeliveryMenController extends Controller
{
    private $deliveryManRepository;

    public function __construct(DeliveryManRepository $repository)
    {
        $this->deliveryManRepository = $repository;
    }

    public function get(Request $request)
    {
        return $this->deliveryManRepository->get($request);
    }

    public function sendDeliveryRequest(Request $request)
    {
        return $this->deliveryManRepository->sendDeliveryRequest($request);
    }

    public function edit(Request $request)
    {
        return $this->deliveryManRepository->edit($request);
    }

    public function free(Request $request)
    {
        return $this->deliveryManRepository->assignFreeDeliveryManToOrder($request);
    }
}
