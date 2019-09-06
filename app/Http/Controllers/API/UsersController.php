<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;

class UsersController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;
    }

    public function create(Request $request)
    {
        return $this->userRepository->create($request);
    }

    public function get(Request $request)
    {
        return $this->userRepository->get($request);
    }

    public function delete(Request $request)
    {
        return $this->userRepository->delete($request);
    }

    public function edit(Request $request)
    {
        return $this->userRepository->edit($request);
    }

    public function getLoginData(Request $request)
    {
        return $this->userRepository->getLoginData($request);
    }

    public function closeSession(Request $request)
    {
        return $this->userRepository->closeSession($request);
    }

    public function resetPassword(Request $request)
    {
        return $this->userRepository->resetPassword($request);
    }

    public function verifyFacebookLogin(Request $request)
    {
        return $this->userRepository->verifyFacebookLogin($request);
    }
}