<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repositories\CellPhoneNumberRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class CellPhoneNumbersController extends Controller
{
	private $repository;

	public function __construct(CellPhoneNumberRepository $repository)
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
}