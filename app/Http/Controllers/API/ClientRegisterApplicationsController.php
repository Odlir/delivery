<?php

namespace App\Http\Controllers\API;

use App\Repositories\ClientRegisterApplicationRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ClientRegisterApplicationsController extends Controller
{
    private $repository;
    
    public function __construct(ClientRegisterApplicationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(Request $request)
    {
        return $this->repository->create($request);
    }

    public function get(Request $request)
    {
        return $this->repository->get($request);
    }

    public function delete(Request $request)
    {
        return $this->repository->delete($request);
    }

}
