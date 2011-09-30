<?php
require_once 'smsToken.php';

class smsSender
{
    protected $sessionId = null;
    protected $dbBackend;
    protected $smsBackend;

    public function __construct($dbBackend, $smsBackend, $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->dbBackend = $dbBackend;
        $this->smsBackend = $smsBackend;
    }

    public function login($username, $password)
    {
        $token = '';

        try
        {
            $token = $this->dbBackend->getToken($username, $password, $_SERVER['REMOTE_ADDR'], $this->sessionId);
        }
        catch (Exception $e)
        {
            throw new Exception('Authentication failed. Reason: ' . $e->getMessage());
        }
        $this->dbBackend->auditLog($_SERVER['REMOTE_ADDR'], 'login', 'Successful login by ' . $username);
        return $token;
    }

    public function send($token, $recipient, $message, $passwordLocations)
    {
        try
        {
            $tokenObj = $this->dbBackend->checkToken($token, $this->sessionId, $_SERVER['REMOTE_ADDR']);
        }
        catch (Exception $e)
        {
            $this->dbBackend->auditLog($_SERVER['REMOTE_ADDR'], 'send', 'Message sending attempt by invalid token ' . $token);
            throw new Exception('Authentication failed. Reason: Bad Token', 0, $e);
        }

        try
        {
            $this->smsBackend->sendSMS($recipient, $message);
            $this->dbBackend->auditLog($_SERVER['REMOTE_ADDR'], 'send', 'Successful SMS sending by ' . $tokenObj->getUsername());
            $this->dbBackend->messageLog($tokenObj->getUserId(), $recipient, $this->maskPasswords($message, $passwordLocations), $_SERVER['REMOTE_ADDR']);
            return 'success';
        }
        catch (PDOException $e)
        {
            error_log('SMS sending cannot be logged due to a database error!');
            $this->dbBackend->auditLog($_SERVER['REMOTE_ADDR'], 'send', 'SMS sending by ' . $tokenObj->getUserName() . ' cannot be logged due to a database error');
        }
        catch (Exception $e)
        {
            $this->dbBackend->auditLog($_SERVER['REMOTE_ADDR'], 'send', 'Error during SMS sending by user ' . $token->getUserName() . ': ' . $e->getMessage());
            error_log('Error during SMS sending: ' . $e->getMessage());
        }
        throw new Exception('Send failed: Unknown Error');
    }

    protected function maskPasswords($message, $passwordLocations)
    {
        $msg = $message;

        foreach ($passwordLocations as $loc)
        {
            $msg = substr_replace($msg, '<masked password>', $loc[0], $loc[1]);
        }

        return $msg;
    }

    public function logout($token)
    {
        try
        {
            $username = $this->dbBackend->removeToken($_SERVER['REMOTE_ADDR'], $this->sessionId, $token);
            $this->dbBackend->auditLog($_SERVER['REMOTE_ADDR'], 'logout', $username . ' logged out successfully');
            session_destroy();
            session_id('');
            unset($_COOKIE['PHPSESSID']);
            return 'success';
        }
        catch (Exception $e)
        {
            error_log('Logout failed: ' . $e->getMessage());
            $this->dbBackend->auditLog('Logout failed: ' . $e->getMessage());
            throw new Exception('Logout failed: Internal Server Error');
        }
    }
}
