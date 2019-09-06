<?php

namespace App\Http\Controllers\API;

use App\Repositories\ClientRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientsController extends Controller
{
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function get(Request $request)
    {
        return $this->repository->get($request);
    }

    public function all(Request $request)
    {
        return $this->repository->all($request);
    }
}
