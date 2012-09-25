<?php
namespace SmsGateway;

use SmsGateway\LoggerInterface;

interface AuthInterface
{
    public function setLogger(LoggerInterface $logger);

    public function getLogger();

    public function getToken($username, $ip, $sessionId);

    public function isTokenValid($token, $ip, $sessionId);

    public function removeToken($token, $ip, $sessionId);
}
