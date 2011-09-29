<?php
require_once 'jsonRPCServer.php';
require_once 'smsSender.php';
require_once 'postgresGatewayBackend.php';

session_start();

//$request_body = file_get_contents('php://input');

//$json = json_decode($request_body);

/* Begin debug dump */

$fd = fopen("/tmp/jsonrpc-test", "w");

fwrite($fd, "\$_SERVER = " . var_export($_SERVER, true) . "\n\n");
fwrite($fd, "\$_GET = " . var_export($_GET, true) . "\n\n");
fwrite($fd, "\$_POST = " . var_export($_POST, true) . "\n\n");
fwrite($fd, "\$_FILES = " . var_export($_FILES, true) . "\n\n");
fwrite($fd, "\$_SESSION = " . var_export($_SESSION, true) . "\n\n");
//fwrite($fd, "\$request_body = '" . $request_body . "';\n");
//fwrite($fd, "\$json = '" . $json . "';\n");

/* End debug dump */

$smsSender = new smsSender(session_id());
jsonRPCServer::handle($smsSender) or fwrite($fd, "No Request\n");

fclose($fd);
