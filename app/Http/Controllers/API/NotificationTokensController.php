<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\NotificationTokenRepository;

class NotificationTokensController extends Controller
{
	private $repository;
    
    public function __construct(NotificationTokenRepository $repository)
    {
    	$this->repository = $repository;
    }

    public function register(Request $request)
    {
    	return $this->repository->register($request);
    }

	public function test()
    {
    	return $this->repository->test();
    }    
}