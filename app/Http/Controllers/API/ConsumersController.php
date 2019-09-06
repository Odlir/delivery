<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repositories\ConsumerRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConsumersController extends Controller
{
	private $repository;
    
    public function __construct(ConsumerRepository $repository)
    {
    	$this->repository = $repository;
    }

    public function addFavoriteRestaurant(Request $request)
    {
    	return $this->repository->addFavoriteRestaurant($request);
    }

    public function deleteFavoriteRestaurant(Request $request)
    {
    	return $this->repository->deleteFavoriteRestaurant($request);
    }

    public function getFavoriteRestaurants(Request $request)
    {
    	return $this->repository->getFavoriteRestaurants($request);
    }

    public function getFavoriteRestaurant(Request $request)
    {
        return $this->repository->getFavoriteRestaurant($request);
    }

}