<?php

namespace App\Models;

class Authorization
{
    protected $token;
    protected $payload;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        if (!$this->token) {
            throw new \Exception('请设置token');
        }
        return $this->token;
    }

    public function getPayload()
    {
        if (!$this->payload) {
            $this->payload = \Auth::setToken($this->getToken())->getPayload();
        }
        return $this->payload;
    }


    public function user()
    {
        return \Auth::authenticate($this->getToken());
    }

    public function toArray()
    {
        return [
            'token' => $this->getToken(),
            'expires_in' => config('jwt.ttl'),
            "refresh_expires_in" => config('jwt.refresh_ttl'),
        ];
    }
}
