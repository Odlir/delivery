<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repositories\DeliveryRequestRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class DeliveryRequestsController extends Controller
{
	private $repository;

	public function __construct(DeliveryRequestRepository $repository)
	{
		$this->repository = $repository;
	}

	public function create(Request $request)
	{
		return $this->repository->create($request);
	}

	public function edit(Request $request)
	{
		return $this->repository->edit($request);
	}

	public function get(Request $request)
	{
		return $this->repository->get($request);
	}
}