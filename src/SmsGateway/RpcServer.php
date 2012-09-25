<?php
namespace SmsGateway;

use Symfony\Component\HttpFoundation\Request;

use SmsGateway\BackendInterface;
use SmsGateway\LoggerInterface;
use SmsGateway\SenderInterface;

class RpcServer
{
    /**
     * The user backend
     *
     * @var SmsGateway\BackendInterface $backend
     */
    private $backend;

    /**
     * The logger
     *
     * @var SmsGateway\LoggerInterface $logger
     */
    private $logger;

    /**
     * The sender
     *
     * @var SmsGateway\SenderInterface $sender
     */
    private $sender;

    public function __construct(BackendInterface $backend, LoggerInterface $logger, SenderInterface $sender)
    {
    }

    protected function login(array $params)
    {
        return true;
    }

    protected function send(array $params)
    {
        return true;
    }

    protected function logout(array $params)
    {
        return true;
    }

    public function handle(Request $request, array $jsonData)
    {
        $params = $jsonData['params'];
        switch ($jsonData['method']) {
            case 'login':
                break;
            case 'send':
                break;
            case 'logout':
                break;
            default:
                    throw new \Exception('Invalid request');
        }
        return 'ajaj';
    }
}