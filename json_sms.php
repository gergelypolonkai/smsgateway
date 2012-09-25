<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

use SmsGateway\Sender\GnokiiSender;
use SmsGateway\Logger\DatabaseLogger;
use SmsGateway\Backend\DatabaseBackend;
use SmsGateway\RpcServer;

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
    $sender = new GnokiiSender();
} catch (Exception $e) {
    $response = new Response('Internal Server Error: Sender cannot be instantiated.', 500);
    $response->send();
    exit;
}

try {
    $logger = new DatabaseLogger('pgsql:host=127.0.0.1;dbname=smsgateway', 'smsgateway', 'Imuiwai8');
} catch (PDOException $e) {
    $response = new Response('Internal Server Error: Logger cannot be instantiated.', 500);
    $response->send();
    exit;
}

try {
    $backend = new DatabaseBackend();
} catch (Exception $e) {
    $response = new Response('Internal Server Error: Backend cannot be instantiated.', 500);
    $response->send();
    exit;
}

$handler = new RpcServer($backend, $logger, $sender);
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
