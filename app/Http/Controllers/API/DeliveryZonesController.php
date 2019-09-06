<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\DeliveryZoneRepository;

class DeliveryZonesController extends Controller
{
    private $deliveryZoneRepository;

    public function __construct(DeliveryZoneRepository $repository)
    {
        $this->deliveryZoneRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->deliveryZoneRepository->create($request);
    }

    public function update(Request $request)
    {
        return $this->deliveryZoneRepository->update($request);
    }
}
