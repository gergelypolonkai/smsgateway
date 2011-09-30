<?php
class smsToken
{
    protected $userId;
    protected $userName;
    protected $sessionId;
    protected $ip;
    protected $token;

    public function __construct($userId, $userName, $sessionId, $ip, $token)
    {
        $this->userId    = $userId;
        $this->userName  = $userName;
        $this->sessionId = $sessionId;
        $this->ip        = $ip;
        $this->token     = $token;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getUserName()
    {
        return $this->userName;
    }
}

