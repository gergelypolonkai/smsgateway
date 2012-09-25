<?php
namespace SmsGateway\Logger;

use SmsGateway\LoggerInterface;
use PDO;

class DatabaseLogger implements LoggerInterface
{
    /**
     * Constructor
     *
     * @param  string $dsn      The PDO datasource string
     * @param  string $username The username to use during the connection
     * @param  string $password The password for the above username
     * @throws PDOException     Upon database connection error
     */
    public function __construct($dsn, $username, $password)
    {
        $this->dbh = new PDO($dsn, $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }

    /**
     * Log an audit event
     *
     * @param  integer $type     The message type
     * @param  string  $username The user this audit log connects to. Can be
     *                           NULL if the user is unauthenticated
     * @param  string  $message  The audit message
     * @return boolean           TRUE if the message was saved, FALSE otherwise
     */
    public function auditLog($type, $username, $message)
    {
    }

    /**
     * Log a sent message
     *
     * @param string  $username  The username who sent this message
     * @param string  $recipient The recipient of the message
     * @param string  $message   The message itself
     */
    public function messageLog($username, $recipient, $message)
    {
    }
}