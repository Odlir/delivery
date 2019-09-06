<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\RestaurantRepository;

class RestaurantsController extends Controller
{
    private $restaurantRepository;

    public function __construct(RestaurantRepository $repository)
    {
        $this->restaurantRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->restaurantRepository->create($request);
    }
    
    public function get(Request $request)
    {
        return $this->restaurantRepository->get($request);
    }

    public function edit(Request $request)
    {
        return $this->restaurantRepository->edit($request);
    }
        
}
