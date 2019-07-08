<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class User extends BaseModel implements AuthenticatableContract, JWTSubject
{
    // 软删除和用户验证attempt
    use SoftDeletes, Authenticatable;

    // 查询用户的时候，不暴露密码
    protected $hidden = ['password', 'deleted_at', 'scopes'];


    // jwt 需要实现的方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    // jwt 需要实现的方法, 一些自定义的参数
    public function getJWTCustomClaims()
    {
        return [];
    }
}
