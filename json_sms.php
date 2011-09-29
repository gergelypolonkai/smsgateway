<?php
require_once 'jsonRPCServer.php';
require_once 'smsSender.php';
require_once 'postgresGatewayBackend.php';

session_start();

try
{
    $backend = new postgresGatewayBackend('localhost', 'sms_gateway', 'quaiy8Zu', 'sms_gateway');
}
catch (PDOException $e)
{
    header('Status: 500 Internal Server Error (DB)');
    exit;
}

try
{
    $smsSender = new smsSender(session_id(), $backend);
}
catch (Exception $e)
{
    header('Status: 500 Internal Server Error (Backend)');
    exit;
}

jsonRPCServer::handle($smsSender);
