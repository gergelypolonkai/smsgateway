<?php
namespace SmsGateway\Logger;

use SmsGateway\LoggerInterface;

/**
 * Description of NullLogger
 *
 * @author polonkai.gergely
 */
class NullLogger implements LoggerInterface
{
    public function auditLog($type, $username, $message)
    {
        return true;
    }

    public function messageLog($username, $recipient, $message, array $passwordLocations)
    {
        return true;
    }
}
