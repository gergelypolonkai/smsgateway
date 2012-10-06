<?php
namespace SmsGateway\Auth;

use SmsGateway\AuthInterface;
use SmsGateway\LoggerInterface;

/**
 * Description of FileAuth
 *
 * @author Gergely Polonkai
 */
class FileAuth implements AuthInterface
{
    private $logger;

    private $sendersFile;

    private $tokenFile;

    public function __construct($sendersFile, $tokenFile) {
        if ($sendersFile == null) {
            throw new \InvalidArgumentException('A senders file path must be passed to the authenticator!');
        }

        if (!is_readable($sendersFile)) {
            throw new \RuntimeException('senders file not readable!');
        }

        if ($tokenFile == null) {
            throw new \InvalidArgumentException('A token file path must be passed to the authenticator!');
        }

        if (
            (
                file_exists($tokenFile)
                && !is_writable($tokenFile)
            )
            || (
                !file_exists($tokenFile)
                && !is_writable(dirname($tokenFile))
            )
        ) {
            throw new \RuntimeException('Token file is not writable!');
        }

        $this->sendersFile = $sendersFile;
        $this->tokenFile = $tokenFile;
    }

    public function authenticate($username, $password, $ip, $sessionId)
    {
        $this->logger->auditLog(LoggerInterface::LOG_AUDIT_LOGIN, $username, "trying to authenticate");
        $lines = file($this->sendersFile);
        foreach ($lines as $line) {
            list($user, $cPassword) = explode(':', trim($line), 2);
            if ($user == $username) {
                if (crypt($password, $cPassword) == $cPassword) {
                    $this->logger->auditLog(LoggerInterface::LOG_AUDIT_LOGIN, $username, "authenticated successfully");

                    return $this->getToken($username, $ip, $sessionId);
                } else {
                    $this->logger->auditLog(LoggerInterface::LOG_AUDIT_LOGIN, $username, "authentication failed: bad password");
                    return false;
                }
            }
        }
        return false;
    }

    public function getTokenUsername($token, $ip, $sessionId)
    {
        $lines = file($this->tokenFile);
        foreach ($lines as $line) {
            list($tokenUser, $tokenIp, $tokenSession, $tokenToken) = explode(':', trim($line), 4);
            if (($tokenToken == $token) && ($tokenIp == $ip) && ($tokenSession == $sessionId)) {
                return $tokenUser;
            }
        }

        return null;
    }

    public function isTokenValid($token, $ip, $sessionId)
    {
        $this->logger->auditLog(LoggerInterface::LOG_AUDIT_LOGIN, null, 'Checking token validity');

        $lines = file($this->tokenFile);
        foreach ($lines as $line) {
            list($tokenUser, $tokenIp, $tokenSession, $tokenToken) = explode(':', trim($line), 4);
            if (($tokenToken == $token) && ($tokenIp == $ip) && ($tokenSession == $sessionId)) {
                return true;
            }
        }

        return false;
    }

    public function getToken($username, $ip, $sessionId) {
        $this->logger->auditLog(LoggerInterface::LOG_AUDIT_LOGIN, $username, "Getting token");

        $lines = file($this->tokenFile);
        foreach ($lines as $line) {
            list($tokenUser, $tokenIp, $tokenSession, $tokenToken) = explode(':', trim($line), 4);
            if (($tokenUser == $username) && ($tokenIp == $ip) && ($tokenSession == $sessionId)) {
                return $tokenToken;
            }
        }

        $token = str_replace(':', '', uniqid('', true));
        $fd = fopen($this->tokenFile, 'a');
        fwrite($fd, sprintf("%s:%s:%s:%s\n", $username, $ip, $sessionId, $token));
        fclose($fd);

        return $token;
    }

    public function removeToken($token, $ip, $sessionId) {
        $username = $this->getTokenUsername($token, $ip, $sessionId);
        $this->logger->auditLog(LoggerInterface::LOG_AUDIT_LOGIN, $username, "Removing token");

        $lines = file($this->tokenFile);
        $fd = fopen($this->tokenFile, 'w');
        foreach ($lines as $line) {
            list($tokenUser, $tokenIp, $tokenSession, $tokenToken) = explode(':', trim($line), 4);
            if (($tokenToken != $token) || ($tokenIp != $ip) || ($tokenSession != $sessionId)) {
                fwrite($fd, sprintf("%s:%s:%s:%s\n", $tokenUser, $tokenIp, $tokenSession, $tokenToken));
            }
        }
        fclose($fd);

        return false;
        return true;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
