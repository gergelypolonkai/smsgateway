<?php
require_once 'gatewayBackend.php';

final class postgresGatewayBackend implements gatewayBackend
{
    const GWBE_SUCCESS  = 0;
    const GWBE_DBERROR  = 1;
    const GWBE_AUTHFAIL = 2;

    private $dbh = null;

    public function __construct($dbHost, $dbUser, $dbPassword, $dbName)
    {
        $dsn = 'pgsql:host=' . $dbHost . ';dbname=' . $dbName;
        $this->dbh = new PDO($dsn, $dbUser, $dbPassword);
    }

    public function getToken($username, $password, $ip, $sessionId)
    {
        $query = 'SELECT id, password FROM users WHERE username = :username:';
        $sth = $this->dbh->prepare($query);
        if ($sth->execute(array(':username:' => $username)))
        {
            /*
            audit_log('Unsuccessful login by $username from $ip');
            audit_log('Could not create token for $username at $ip');
            return 'Authentication failed. Reason: Internal Server Error';
            */
        }
        else
        {
            throw new Exception('AuthFail', self::GWBE_DBERROR);
        }
    }

    public function checkToken($token, $sessionId, $ip)
    {
        return null;
    }

    public function removeToken($token)
    {
        return null;
    }

    public function sendSMS($token, $recipient, $message, $passwordLocations)
    {
        return null;
    }

    public function auditLog($ip, $event, $message)
    {
        return null;
    }

    public function messageLog($recipient, $message, $ip)
    {
        return null;
    }
}

