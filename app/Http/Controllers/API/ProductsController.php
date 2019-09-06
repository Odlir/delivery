<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;

class ProductsController extends Controller
{
    private $productRepository;

    public function __construct(ProductRepository $repository)
    {
        $this->productRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->productRepository->create($request);
    }

    public function get(Request $request)
    {
        return $this->productRepository->get($request);
    }

    public function edit(Request $request)
    {
        return $this->productRepository->edit($request);
    }

    public function delete(Request $request)
    {
        return $this->productRepository->delete($request);
    }

    public function all(Request $request)
    {
        return $this->productRepository->get($request);
    }

    public function createOffer(Request $request)
    {
        return $this->productRepository->createOffer($request);
    }
}
