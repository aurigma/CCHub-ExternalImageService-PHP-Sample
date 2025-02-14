<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function getUserId(): ?int
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $user ? $user->id : null;
    }
}