<?php
namespace SmsGateway\Auth;

use SmsGateway\AuthInterface;
use SmsGateway\LoggerInterface;

class DatabaseAuth implements AuthInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger) {
        throw new \Exception("This authenticator is not implemented yet!");
    }

    public function isTokenValid($token, $ip, $sessionId) {
        return false;
    }

    public function getToken($username, $ip, $sessionId) {
        return null;
    }

    public function removeToken($token, $ip, $sessionId) {
        return true;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
