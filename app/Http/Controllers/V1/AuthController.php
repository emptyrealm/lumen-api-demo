<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Authorization;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * @api {post} /v1/auth 创建一个token
     * @apiDescription 创建一个token
     * @apiGroup auth
     * @apiPermission none
     * @apiParam {Email} email     邮箱
     * @apiParam {String} password  密码 请先用base64将明文加密后传输过来
     * @apiVersion  1.0.1
     * @apiParamExample  {form} Request-Example:
     *  email:your email
     *  password:your password
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sdW1lbi1hcGkubG9jYWxcL3YxXC9hdXRoIiwiaWF0IjoxNTYyNTU3MTkzLCJleHAiOjE1NjI1NjA3OTMsIm5iZiI6MTU2MjU1NzE5MywianRpIjoicmE0MHBNWlE2NUtLd3F4OCIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInNjb3BlcyI6bnVsbH0.yDvkkPMy_BPn1XQIUxpLE476FVVe6p8yTkQ0KmGoMjI",
     *        "expires_in": 60,
     *        "refresh_expires_in": 20160
     *     }
     * @apiSuccess {String} token 凭证,用于之后的通信凭证
     * @apiSuccess {Number} expires_in 当前token有效期
     * @apiSuccess {Number} refresh_expires_in token刷新有效期
     * 
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 401
     *     {
     *       "error": "用户面密码错误"
     *     }
     */
    public function token(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $credentials = $request->only('email', 'password');

        //先base64 decode
        if (isset($credentials['password'])) {
            $credentials['password'] = base64_decode($credentials['password']);
        }

        if (!$token = \Auth::attempt($credentials)) {
            $this->response->errorUnauthorized(trans('auth.incorrect'));
        }

        //将scopes存入，作简单的权限限制
        $customClaims = ['scopes' => \Auth::user()->scopes];

        $token = JWTAuth::claims($customClaims)->attempt($credentials);
        $authorization = new Authorization($token);
        return $this->response->array($authorization->toArray())->setStatusCode(201);
    }
    /**
     * @api {put} /v1/auth/token 刷新token
     * @apiDescription 刷新token
     * @apiGroup auth
     * @apiPermission jwt
     * @apiVersion  1.0.1
     * @apiHeader {String} Authorization 用户旧的jwt-token, value以Bearer 开头
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer  eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9pY21hbGxhcGkubG9jYWxcL3YxXC9hdXRoXC90b2tlbiIsImlhdCI6MTU2MTcyMjM5OCwiZXhwIjoxNTYxNzI2MDExLCJuYmYiOjE1NjE3MjI0MTEsImp0aSI6InIzMDc1Mjg0eVdybkNhSnQiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjciLCJzY29wZXMiOiJlZGl0LWVycE5vIn0.AfED_DGQVD1bcE9ixsaqdU8BDzho-wZNugByrm5P2gI"
     *     }
     * @apiSuccess {String} token 凭证,用于之后的通信凭证
     * @apiSuccess {Number} expires_in 当前token有效期
     * @apiSuccess {Number} refresh_expires_in token刷新有效期
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sdW1lbi1hcGkubG9jYWxcL3YxXC9hdXRoIiwiaWF0IjoxNTYyNTU3MTkzLCJleHAiOjE1NjI1NjA3OTMsIm5iZiI6MTU2MjU1NzE5MywianRpIjoicmE0MHBNWlE2NUtLd3F4OCIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInNjb3BlcyI6bnVsbH0.yDvkkPMy_BPn1XQIUxpLE476FVVe6p8yTkQ0KmGoMjI",
     *        "expires_in": 60,
     *        "refresh_expires_in": 20160
     *     }
     */
    public function refresh()
    {
        $authorization = new Authorization(\Auth::refresh());
        return $this->response->array($authorization->toArray());
    }
    /**
     * @api {delete} /v1/auth/token 删除当前token
     * @apiDescription 删除当前token
     * @apiGroup auth
     * @apiPermission jwt
     * @apiVersion  1.0.1
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     */
    public function destroy()
    {
        \Auth::logout();
        return $this->response->noContent();
    }
}
