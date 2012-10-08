<?php
namespace SmsGateway\Sender;

use SmsGateway\SenderInterface;
use SmsGateway\LoggerInterface;

class GnokiiSender implements SenderInterface
{
    /**
     *
     * @var SmsGateway\LoggerInterface $logger
     */
    private $logger;

    private $gnokiiPath;

    public function __construct($gnokiiPath)
    {
        if (!is_executable($this->gnokiiPath)) {
            throw new \Exception('Specified Gnokii executable is not executable by me!');
        }

        $this->gnokiiPath = $gnokiiPath;
    }

    public function send($username, $recipient, $message, array $passwordLocations)
    {
        if ($this->logger === null) {
            throw new \LogicException('Logger is not defined!');
        }

        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $cmd = escapeshellcmd($this->gnokiiPath . ' --sendsms ' . escapeshellarg($recipient));
        $pipes = array();
        $process = proc_open($cmd, $descriptors, $pipes, null, array('LANG' => 'en_US.UTF-8'));
        if (is_resource($process)) {
            fwrite($pipes[0], $message);
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            $returnValue = proc_close($process);

            if ($returnValue != 0) {
                throw new \RuntimeException('Unable to send SMS: ' . $stderr . '; ' . $stdout);
            }

            $this->logger->messageLog($username, $recipient, $message, $passwordLocations);
        } else {
            throw new \RuntimeException('Unable to start Gnokii.');
        }

        return true;
    }

    public function setLogger(LoggerInterface $logger) {
        if ($logger === null) {
            throw new \RuntimeException('A logger interface must be specified!');
        }

        $this->logger = $logger;
    }

    public function getLogger() {
        return $this->logger;
    }
}
