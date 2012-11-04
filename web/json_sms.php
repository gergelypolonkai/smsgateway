<?php
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Yaml\Yaml;

use SmsGateway\RpcServer;

$configFile = __DIR__ . '/../app/config/config.yml';

$globalConfig = Yaml::parse($configFile);
if (!array_key_exists('sms_gateway', $globalConfig)) {
    error_log($configFile . ' does not contain the sms_gateway level!');
    die('Invalid configuration file!');
}

$config = $globalConfig['sms_gateway'];
if (!array_key_exists('sender_class', $config)) {
    error_log($configFile . ' does not specify sender_class!');
    die('Invalid configuration file!');
}
$senderClass = $config['sender_class'];

if (!array_key_exists('auth_class', $config)) {
    error_log($configFile . ' does not specify auth_class!');
    die('Invalid configuration file!');
}
$authClass = $config['auth_class'];

if (!array_key_exists('logger_class', $config)) {
    error_log($configFile . ' does not specify logger_class!');
    die('Invalid configuration file!');
}
$loggerClass = $config['logger_class'];

$request = Request::createFromGlobals();

$routes = new RouteCollection();
$routes->add('jsonHandle', new Route('/', array('controller' => 'handleJson'), array('_method' => 'POST')));

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);
try {
    $parameters = $matcher->match($request->getPathInfo());
} catch (ResourceNotFoundException $e) {
    $response = new Response('Bad Request', 400);
    $response->send();
    exit;
} catch (MethodNotAllowedException $e) {
    $response = new Response('Bad Request', 400);
    $response->send();
    exit;
}

if ($request->getContentType() != 'json') {
    $response = new Response('Bad Request', 400);
    $response->send();
    exit;
}

$jsonData = json_decode($request->getContent(), true);
if ($jsonData === null) {
    $response = new Response('Bad Request', 400);
    $response->send();
    exit;
}
if (!array_key_exists('method', $jsonData) || !array_key_exists('params', $jsonData) || !is_array($jsonData['params'])) {
    $response = new Response('Bad Request', 400);
    $response->send();
    exit;
}
$wantResponse = (!empty($jsonData['id']));

try {
    $sender = new $senderClass('/var/www/html/smsgateway/app/cache/sms_spool');
} catch (Exception $e) {
    $response = new Response('Internal Server Error: Sender cannot be instantiated.', 500);
    $response->send();
    exit;
}

try {
    $logger = new $loggerClass('/var/www/html/smsgateway/app/logs/sms-message.log', '/var/www/html/smsgateway/app/logs/sms-audit.log');
} catch (LogicException $e) {
    $response = new Response('Internal Server Error: Logger cannot be instantiated.', 500);
    $response->send();
    exit;
}

try {
    $auth = new $authClass('/var/www/html/smsgateway/senders', '/var/www/html/smsgateway/app/cache/tokens');
} catch (Exception $e) {
    $response = new Response('Internal Server Error: Authenticator cannot be instantiated.', 500);
    $response->send();
    exit;
}

$auth->setLogger($logger);
$sender->setLogger($logger);

$handler = new RpcServer($auth, $logger, $sender);

try {
    $result = $handler->handle($request, $jsonData);
} catch (Exception $e) {
    if ($wantResponse) {
        $jsonResponse = array(
            'id' => $jsonData['id'],
            'result' => null,
            'error' => $e->getMessage(),
        );
        $response = new Response(json_encode($jsonResponse));
        $response->send();
    }
    exit;
}

if ($wantResponse) {
    $jsonResponse = array(
        'id' => $jsonData['id'],
        'result' => $result,
        'error' => null,
    );

    $response = new Response(json_encode($jsonResponse), 200, array('Content-Type' => 'application/json'));
    $response->send();
}
