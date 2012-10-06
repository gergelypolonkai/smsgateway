<?php
namespace SmsGateway;

use Symfony\Component\HttpFoundation\Request;

use SmsGateway\AuthInterface;
use SmsGateway\LoggerInterface;
use SmsGateway\SenderInterface;

class RpcServer
{
    /**
     * The user backend
     *
     * @var SmsGateway\AuthInterface $backend
     */
    private $auth;

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

    public function __construct(AuthInterface $auth, LoggerInterface $logger, SenderInterface $sender)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->sender = $sender;
    }

    protected function login($username, $password)
    {
        $token = $this->auth->authenticate($username, $password, $_SERVER['REMOTE_ADDR'], null);

        if ($token === false) {
            throw new \Exception('Could not create token.');
        }

        return $token;
    }

    protected function send($token, $recipient, $message, array $passwordLocations)
    {
        if (!$this->auth->isTokenValid($token, $_SERVER['REMOTE_ADDR'], null)) {
            throw new \Exception('Invalid token!');
        }

        $this->sender->send($this->auth->getTokenUsername($token, $_SERVER['REMOTE_ADDR'], null), $recipient, $message, $passwordLocations);

        // TODO: Send the message!

        return 'success';
    }

    protected function logout($token)
    {
        if (!$this->auth->isTokenValid($token, $_SERVER['REMOTE_ADDR'], null)) {
            throw new \Exception('Invalid token!');
        }

        $this->auth->removeToken($token, $_SERVER['REMOTE_ADDR'], null);

        return 'success';
    }

    public function handle(Request $request, array $jsonData)
    {
        $params = $jsonData['params'];
        switch ($jsonData['method']) {
            case 'login':
                if (count($params) != 2) {
                    throw new \InvalidArgumentException('Bad parameter count!');
                }

                return $this->login($params[0], $params[1]);

                break;
            case 'send':
                if (count($params) != 4) {
                    throw new \InvalidArgumentException('Bad parameter count!');
                }
                if (!is_array($params[3])) {
                    throw new \InvalidArgumentException('Invalid 4th parameter!');
                }

                return $this->send($params[0], $params[1], $params[2], $params[3]);

                break;
            case 'logout':
                if (count($params) != 1) {
                    throw new \InvalidArgumentException('Bad parameter count!');
                }

                return $this->logout($params[0]);
                break;
            default:
                throw new \BadMethodCallException('Unknown method ' . $jsonData['method']);
                break;
        }
    }
}