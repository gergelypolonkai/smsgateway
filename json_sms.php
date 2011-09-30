<?php
require_once 'jsonRPCServer.php';
require_once 'smsSender.php';
require_once 'postgresGatewayBackend.php';
require_once 'gnokiiSMSBackend.php';

session_start();

try
{
    $dbBackend = new postgresGatewayBackend('localhost', 'sms_gateway', 'quaiy8Zu', 'sms_gateway');
}
catch (PDOException $e)
{
    header('Status: 500 Internal Server Error (DB)');
    exit;
}

try
{
    $smsBackend = new gnokiiSMSBackend();
}
catch (Exception $e)
{
    header('Status: 500 Internal Server Error (SMS)');
    exit;
}

try
{
    $smsSender = new smsSender($dbBackend, $smsBackend, session_id());
}
catch (Exception $e)
{
    header('Status: 500 Internal Server Error');
    exit;
}

jsonRPCServer::handle($smsSender);
