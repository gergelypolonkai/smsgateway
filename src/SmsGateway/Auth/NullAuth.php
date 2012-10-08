<?php
namespace SmsGateway\Auth;

use SmsGateway\AuthInterface;
use SmsGateway\LoggerInterface;

/**
 * Description of NullAuth
 *
 * @author Gergely Polonkai
 */
class NullAuth implements AuthInterface
{
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function removeToken($token, $ip, $sessionId)
    {
        return true;
    }

    public function isTokenValid($token, $ip, $sessionId)
    {
        return true;
    }

    public function authenticate($username, $password, $ip, $sessionId)
    {
        return true;
    }

    public function getTokenUsername($token, $ip, $sessionId)
    {
        return 'unknown';
    }

    public function getToken($username, $ip, $sessionId)
    {
        return 'alwaysValid';
    }
}
