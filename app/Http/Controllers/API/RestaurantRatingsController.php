<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\RestaurantRatingRepository;

class RestaurantRatingsController extends Controller
{
    private $restaurantRatingRepository;
    
    public function __construct(RestaurantRatingRepository $repository)
    {
        $this->restaurantRatingRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->restaurantRatingRepository->create($request);
    }

    public function get(Request $request)
    {
        return $this->restaurantRatingRepository->get($request);
    }

    public function edit(Request $request)
    {
        return $this->restaurantRatingRepository->edit($request);
    }
}