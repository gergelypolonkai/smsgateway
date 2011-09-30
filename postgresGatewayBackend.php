<?php
require_once 'gatewayBackend.php';
require_once 'smsToken.php';

final class postgresGatewayBackend implements gatewayBackend
{
    const GWBE_SUCCESS  = 0;
    const GWBE_DBERROR  = 1;
    const GWBE_AUTHFAIL = 2;
    const GWBE_INACTIVE = 3;
    const GWBE_SERVERR  = 4;

    private $dbh = null;

    public function __construct($dbHost, $dbUser, $dbPassword, $dbName)
    {
        $dsn = 'pgsql:host=' . $dbHost . ';dbname=' . $dbName . ';user=' . $dbUser . ';password=' . $dbPassword;
        try
        {
            $this->dbh = new PDO($dsn, $dbUser, $dbPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch (PDOException $e)
        {
            error_log('Unable to connect to database: ' . $e->getMessage());
        }
    }

    private function tokenExists($token)
    {
        $query = 'SELECT id FROM tokens WHERE token = ?';
        $sth = $this->dbh->prepare($query);
        $sth->execute($token);
        $row = $sth->fetch();
        return ($row !== false);
    }

    private function generateToken()
    {
        do
        {
            $str = '                                ';
            for ($i = 0; $i < 32; $i++)
                $str[$i] = chr(rand(32, 126));
            $str = sha1($str);
        }
        while ($this->tokenExists($str));

        return $str;
    }

    protected function refreshToken($token)
    {
        try
        {
            $query = 'UPDATE tokens SET start_time = NOW() WHERE token = ?';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($token));
        }
        catch (PDOException $e)
        {
        }
    }

    public function getToken($username, $password, $ip, $sessionId)
    {
        $urec = array();
        try
        {
            $query = 'SELECT id, valid, password FROM users WHERE username = ?';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($username));
            $urec = $sth->fetch();

            if ($urec === false)
            {
                $this->auditLog($ip, 'login', 'Unsuccessful login with unknown username ' . $username);
                throw new Exception('Authentication failure', self::GWBE_AUTHFAIL);
            }
            if ($urec['valid'] == false)
            {
                $this->auditLog($ip, 'login', 'Unsuccessful login with disabled username ' . $username);
                throw new Exception('Authentication failure', self::GWBE_INVALID);
            }
            if ($urec['password'] != crypt($password, $urec['password']))
            {
                $this->auditLog($ip, 'login', 'Unsuccessful login with bad password for ' . $username);
                throw new Exception('Authentication failure', self::GWBE_AUTHFAIL);
            }
        }
        catch (PDOException $e)
        {
            throw new Exception('AuthFail: ' . $e->getMessage(), self::GWBE_DBERROR, $e);
        }

        try
        {
            $query = 'SELECT ip, token FROM tokens WHERE session_id = ?';
            $sth = $this->dbh->prepare($query);
            $sth->execute($sessionId);
            $row = $sth->fetch();
            if ($row !== false)
            {
                if ($row['ip'] != $ip)
                {
                    throw new Exception('Authentication failed. Reason: session used from wrong IP address');
                }
                $this->refreshToken($token);
                return $row['token'];
            }
        }
        catch (PDOException $e)
        {
            throw new Exception('Authentication failed. Reason: Internal Server Error', 0, $e);
        }

        try
        {
            $token = $this->generateToken();
            $query = 'INSERT INTO tokens (ip, session_id, token, start_time, user_id) VALUES (?, ?, ?, NOW(), ?)';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($ip, $sessionId, $token, $urec['id']));
            $this->auditLog($ip, 'login', 'Successful login by ' . $username);
            return $token;
        }
        catch (PDOException $e)
        {
            error_log('Database error: ' . $e->getMessage());
            $this->auditLog($ip, 'login', 'Unable to save token for ' . $username . ': ' . $e->getMessage());
            throw new Exception('Authentication failed. Reason: Internal Server Error');
        }

        /* How did we get here??? */
        error_log('Unknown Error in getToken()');
        throw new Exception('Unknown Error');
    }

    public function checkToken($token, $sessionId, $ip)
    {
        try
        {
            $query = 'SELECT users.id AS uid, users.username AS uname FROM tokens LEFT JOIN users ON users.id = tokens.user_id WHERE ip = ? AND session_id = ? AND token = ? AND start_time + interval \'1 hour\' > now()';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($ip, $sessionId, $token));
            $row = $sth->fetch();
            if ($row === false)
            {
                throw new Exception('Authentication failed. Reason: Invalid Token');
            }
            $smsToken = new smsToken($row['uid'], $row['uname'], $sessionId, $ip, $token);
            return $smsToken;
        }
        catch (PDOException $e)
        {
            throw new Exception('Authentication failed. Reason: Internal Server Error', 0, $e);
        }

        error_log('Unknown Error in checkToken()');
        throw new Exception('Authentication failed. Reason: Unknown Error');
    }

    public function removeToken($ip, $sessionId, $token)
    {
        try
        {
            $tokenObj = $this->checkToken($token, $sessionId, $ip);
            $query = 'DELETE FROM tokens WHERE token = ?';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($token));
            return $tokenObj->getUserName();
        }
        catch (PDOException $e)
        {
            throw new Exception('Logout failed. Reasone: Internal Server Error');
        }
        catch (Exception $e)
        {
            throw new Exception('Authentication failed. Reason: Bad Token');
        }
    }

    public function auditLog($ip, $event, $message)
    {
        try
        {
            $query = 'INSERT INTO audit_log (id, time, ip, event, message) VALUES (DEFAULT, NOW(), ?, ?, ?)';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($ip, $event, $message));
        }
        catch (PDOException $e)
        {
           error_log('Database error during SMS Audit Logging: ' . $e->getMessage());
        }
    }

    public function messageLog($senderId, $recipient, $message, $ip)
    {
        try
        {
            $query = 'INSERT INTO log (id, sender, recipient, time, message, ip) VALUES (DEFAULT, ?, ?, NOW(), ?, ?)';
            $sth = $this->dbh->prepare($query);
            $sth->execute(array($senderId, $recipient, $message, $ip));
        }
        catch (PDOException $e)
        {
        }
    }
}

