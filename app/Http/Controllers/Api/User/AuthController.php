<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use App\Http\Services\UserService;
use App\Http\Requests\User\AuthRequest;
use App\Http\Requests\User\RegisterRequest;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login(AuthRequest $request)
    {
        $data = $request->all();
        $data['guard'] = 'user';
        return $this->userService->login($data);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->all();
        $data['guard'] = 'user';
        $data['is_online'] = 1;
        return $this->userService->register($data);
    }


    public function userInfo()
    {
        return $this->success(data:auth()->user());
    }

    public function changePassword(ChangePasswordRequest $request){
        return $this->userService->changePassword($request->all());
    }
}
