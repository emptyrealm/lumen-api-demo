<?php namespace App\Services;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserScopes {

    const INSERT = 'insert';
    const DELETE = 'delete';
    const SELECT = 'select';
    const UPDATE = 'update';    

    static function inScopes($scope){
        $scopes=\Tymon\JWTAuth\Facades\JWTAuth::parseToken()->getClaim('scopes');
        $arr=explode(",",$scopes);
        if(is_array($arr)){
            return in_array($scope,$arr);
        }
        return false;
    }
    
    //检查是否有权限
    static function checkScopes($scope){
        if (!self::inScopes($scope)) {
            throw new AccessDeniedHttpException(trans('auth.permission'));
        }
    }

}