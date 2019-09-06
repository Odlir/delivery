<?php

namespace App\Http\Controllers\API;

use App\Repositories\ResourceRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ResourcesController extends Controller
{
    private $repository;

    public function __construct(ResourceRepository $repository)
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
}
