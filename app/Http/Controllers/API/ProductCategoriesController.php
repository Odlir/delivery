<?php

namespace App\Http\Controllers\API;

use App\Repositories\ProductCategoryRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductCategoriesController extends Controller
{
    private $repository;
    
    public function __construct(ProductCategoryRepository $repository)
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

    public function edit(Request $request)
    {
        return $this->repository->edit($request);
    }
}
