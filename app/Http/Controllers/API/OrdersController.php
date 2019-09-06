<?php

namespace App\Http\Controllers\API;

use App\Repositories\OrderRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class OrdersController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepository $repository)
    {
        $this->orderRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->orderRepository->create($request);
    }

    public function edit(Request $request)
    {
        return $this->orderRepository->edit($request);
    }

    public function get(Request $request)
    {
        return $this->orderRepository->get($request);
    }
}
