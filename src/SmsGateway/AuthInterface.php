<?php
namespace SmsGateway;

use SmsGateway\LoggerInterface;

interface AuthInterface
{
    public function setLogger(LoggerInterface $logger);

    public function getLogger();

    public function authenticate($username, $password, $ip, $sessionId);

    public function getToken($username, $ip, $sessionId);

    public function isTokenValid($token, $ip, $sessionId);

    public function removeToken($token, $ip, $sessionId);

    public function getTokenUsername($token, $ip, $sessionId);
}
