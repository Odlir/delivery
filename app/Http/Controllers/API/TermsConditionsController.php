<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\TermsConditionsRepository;

class TermsConditionsController extends Controller
{
    private $repository;


    public function __construct(TermsConditionsRepository $repository)
    {
    	$this->repository = $repository;
    }

    public function get()
    {
    	return $this->repository->get();
    }
}