<?php

namespace App\Api\V1\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Auth;

class LoginController extends Controller
{
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);

        try {
            $token = Auth::guard()->attempt($credentials);
            if(!$token) {
                throw new AccessDeniedHttpException(trans('http.unauthorized'));
            }

        } catch (JWTException $e) {
            throw new HttpException(trans('http.internal-server-error'));
        }

        return response()
            ->json([
                'status_code' => 200,
                'message' => trans('login.success'),
                'data' => [
                    'token' => $token,
                    'expires_in' => Auth::guard()->factory()->getTTL() * 60
                ]
            ], 200);
    }
}
