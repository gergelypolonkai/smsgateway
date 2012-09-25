<?php
namespace SmsGateway;

interface SenderInterface
{
    /**
     *
     * @param  string $recipient
     * @param  string $message
     * @return boolean           true upon success. On error, throws exceptions.
     * @throws Exception         Upon sending error. Gnokii output will be
     *                           stored in $e->message
    public function send($recipient, $message);
}
