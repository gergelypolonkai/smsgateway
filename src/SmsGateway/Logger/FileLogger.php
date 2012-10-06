<?php
namespace SmsGateway\Logger;

use SmsGateway\LoggerInterface;

/**
 * Description of FileLogger
 *
 * @author Gergely Polonkai
 */
class FileLogger implements LoggerInterface
{
    const PASSWORD_MASK = '[password]';

    /**
     * The message log file
     *
     * @var resource $messageLogHandle
     */
    private $messageLogHandle;

    /**
     * The audit log file
     *
     * @var resource $auditLogHandle
     */
    private $auditLogHandle;

    /**
     * 
     * @param string $messageLogFile Name of the message log file
     * @param string $auditLogFile   Name of the audit log file
     * @throws \LogicException       Upon file opening error
     */
    public function __construct($messageLogFile, $auditLogFile)
    {
        if ($messageLogFile == null) {
            throw new \LogicException('Message log file can not be null!');
        }

        if ($auditLogFile == null) {
            throw new \LogicException('Audit log file can not be null!');
        }

        if (
            (
                file_exists($messageLogFile)
                && !is_writable($messageLogFile)
            )
            || (
                !file_exists($messageLogFile)
                && !is_writable(dirname($messageLogFile))
            )
        ) {
            throw new \LogicException('Message log file is not writable!');
        }

        if (
            (
                file_exists($auditLogFile)
                && !is_writable($auditLogFile)
            )
            || (
                !file_exists($auditLogFile)
                && !is_writable(dirname($auditLogFile))
            )
        ) {
            throw new \LogicException('Audit log file is not writable!');
        }

        if (($this->messageLogHandle = fopen($messageLogFile, 'a')) === false) {
            throw new \LogicException('Message log file could not be opened!');
        }

        if (($this->auditLogHandle = fopen($auditLogFile, 'a')) === false) {
            throw new \LogicException('Audit log file could not be opened!');
        }
    }

    public function __destruct() {
        fclose($this->messageLogHandle);
        fclose($this->auditLogHandle);
    }

    private function orderPasswordLocations($a, $b)
    {
        if ($a[0] == $b[0]) {
            return 0;
        } elseif ($a[0] < $b[0]) {
            return -1;
        }
        return 1;
    }

    public function messageLog($username, $recipient, $message, array $passwordLocations)
    {
        usort($passwordLocations, array($this, 'orderPasswordLocations'));

        $encodedMessage = $message;
        $mod = 0;
        foreach ($passwordLocations as $loc) {
            list($pos, $length) = $loc;

            $encodedMessage = substr_replace($encodedMessage, self::PASSWORD_MASK, $pos + $mod, $length);
            $mod += (strlen(self::PASSWORD_MASK) - $length);
        }

        $logMessage = "From $username
From: $username
To: $recipient

$encodedMessage\n\n";

        fwrite($this->messageLogHandle, $logMessage);
        fflush($this->messageLogHandle);
        return true;
    }

    public function auditLog($type, $username, $message)
    {
        if ($username === null) {
            $logMessage = "$message\n";
        } else {
            $logMessage = "$username: $message\n";
        }
        fwrite($this->auditLogHandle, $logMessage);
        fflush($this->auditLogHandle);
        return true;
    }
}
