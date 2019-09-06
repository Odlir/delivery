<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\AddressRepository;

class AddressesController extends Controller
{
    private $addressRepository;

    public function __construct(AddressRepository $repository)
    {
        $this->addressRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->addressRepository->create($request);
    }

    public function get(Request $request)
    {
        return $this->addressRepository->get($request);
    }

    public function edit(Request $request)
    {
        return $this->addressRepository->edit($request);
    }

    public function delete(Request $request)
    {
        return $this->addressRepository->delete($request);
    }
    
}
