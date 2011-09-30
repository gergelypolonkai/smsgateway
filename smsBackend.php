<?php
interface smsBackend
{
    public function sendSMS($recipient, $message);
}

