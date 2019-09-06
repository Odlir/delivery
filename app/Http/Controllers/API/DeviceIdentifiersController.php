<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repositories\DeviceIdentifierRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class DeviceIdentifiersController extends Controller
{
	private $repository;

	public function __construct(DeviceIdentifierRepository $repository)
	{
		$this->repository = $repository;
	}

    public function create(Request $request)
    {
    	return $this->repository->create($request);
    }
}