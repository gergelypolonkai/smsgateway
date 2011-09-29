<?php
class smsSender
{
    protected $sessionId = null;
    protected $backend;

    public function __construct($backend, $sessionId)
    {
        $this->sessionId = $sessionId;
        $this->backend = $backend;
    }

    public function login($username, $password)
    {
        try
        {
            $token = $this->backend->getToken($username, $password, $_SERVER['REMOTE_ADDR'], $this->sessionId);
        }
        catch (Exception $e)
        {
            throw new Exception('Authentication failed. Reason: ' . $e->getMessage());
        }
        $this->backend->auditLog($_SERVER['REMOTE_ADDR'], 'login', 'Successful login by ' . $username);
        return $token;
    }

    public function send($token, $recipient, $message, $passwordLocations)
    {
        /*
        if (valid_token($token)
        {
            if (send_sms($recipient, $message))
            {
                audit_log('Successful message sending by $token->username at $ip');
                message_log('$message successfully sent to $recipient');
            }
            else
            {
                audit_log('Message sending failed for $token->username at $ip');
            }
        }
        else
        {
            audit_log('Message sending attempt from $ip with invalid token');
            throw new Exception('Authentication failed. Reason: Invalid Token');
        }
        */
        /* TODO: implement */
        throw new Exception('This feature is not yet implemented');
    }

    public function logout($token)
    {
        /*
        delete_token($token);
        audit_log('$token->username logged out at $ip');
        return 'success';
        */
        /* TODO: implement */
        throw new Exception('This feature is not yet implemented');
    }
}
