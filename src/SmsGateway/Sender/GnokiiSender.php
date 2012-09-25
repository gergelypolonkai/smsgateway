<?php
namespace SmsGateway\Sender;

use SmsGateway\SenderInterface;

class GnokiiSender implements SenderInterface
{
    private $gnokiiPath;

    public function __construct($gnokiiPath)
    {
        $this->gnokiiPath = $gnokiiPath;

        if (!is_executable($this->gnokiiPath)) {
            throw new \Exception('Specified Gnokii executable is not executable by me!');
    }

    public function send($recipient, $message)
    {
        return true;
    }
}
