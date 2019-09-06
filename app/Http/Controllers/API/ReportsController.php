<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Repositories\ReportRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
	private $repository;

	public function __construct(ReportRepository $repository)
	{
		$this->repository = $repository;
	}

	public function getDaySales(Request $request)
	{
		return $this->repository->getDaySales($request);
	}
}