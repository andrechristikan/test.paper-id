<?php

namespace App\Api\V1\Controllers;

use Config;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SignUpController extends Controller
{
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());
        if(!$user->save()) {
            throw new HttpException(trans('http.internal-server-error'));
        }

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status_code' => 201,
                'message' => trans('sign-up.success'),
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);

        return response()->json([
            'status_code' => 201,
            'message' => trans('sign-up.success'),
            'data' => [
                'token' => $token
            ]
        ], 201);
    }
}
