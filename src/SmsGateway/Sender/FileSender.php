<?php
namespace SmsGateway\Sender;

use SmsGateway\SenderInterface;
use SmsGateway\LoggerInterface;

/**
 * Description of FileSender
 *
 * @author Gergely Polonkai
 */
class FileSender implements SenderInterface
{
    private $messageDir;

    /**
     * @var SmsGateway\LoggerInterface $logger
     */
    private $logger;

    public function __construct($messageDir)
    {
        if (file_exists($messageDir) && !is_dir($messageDir)) {
            throw new \InvalidArgumentException('Message directory specified is not a directory!');
        }

        if (!file_exists($messageDir) && !is_writable(dirname($messageDir))) {
            throw new \RuntimeException('Message directory cannot be created');
        }

        if (!file_exists($messageDir)) {
            mkdir($messageDir, 0777, true);
        }

        if (!is_writable($messageDir)) {
            throw new \RuntimeException('Message directory is not writable!');
        }

        $this->messageDir = $messageDir;
    }

    public function setLogger(LoggerInterface $logger) {
        if ($logger === null) {
            throw new \InvalidArgumentException('A logger must be passed to the authenticator!');
        }

        $this->logger = $logger;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function send($username, $recipient, $message, array $passwordLocations)
    {
        $rcptDir = $this->messageDir . '/' . $recipient;

        if (file_exists($rcptDir) && (!is_writable($rcptDir) || !is_dir($rcptDir))) {
            throw new \RuntimeException('Message directory is not writable!');
        }
        if (!file_exists($rcptDir)) {
            mkdir($rcptDir);
        }

        $messageFileName = date('YmdHis') . '-' . uniqid() . '.sms';
        $fd = fopen($rcptDir . '/' . $messageFileName, 'w');
        fwrite($fd, $message);
        fclose($fd);

        $this->logger->messageLog($username, $recipient, $message, $passwordLocations);

        return true;
    }
}
