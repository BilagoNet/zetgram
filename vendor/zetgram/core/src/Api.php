<?php

namespace Zetgram;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Zetgram\Exceptions\UndefinededException;

use function Swoole\Coroutine\Http\post;
use function Swoole\Coroutine\Http\get;

class Api extends ApiAbstract
{
    protected const API_END_POINT = 'http://telegram-bot-api:8081/bot';

    /**
     * @var HttpClient
     */
    protected HttpClient $client;

    /**
     * @var string
     */
    protected string $api_url;

    /**
     * @var ExceptionHandler
     */
    protected ExceptionHandler $exceptionHandler;

    /**
     * Api constructor.
     * @param HttpClient $client
     * @param string $token
     * @param ExceptionHandler $exceptionHandler
     */
    public function __construct(HttpClient $client, string $token, ExceptionHandler $exceptionHandler)
    {
        $this->client = $client;
        $this->api_url = self::API_END_POINT . $token . '/';
        $this->exceptionHandler = $exceptionHandler;
    }

    protected function sendRequest(string $uri, array $data = [])
    {
        if (empty($data)) {
            return $this->getRequest($uri);
        }
        return $this->postRequest($uri, $data);
    }

    protected function request($method, $data = [])
    {
        $uri = $this->api_url . $method;
        try {
            $response = $this->sendRequest($uri, $data);
            $body = $response->getBody();
            $data = json_decode($body);
            return ($data->ok) ? $data->result : $data;
        } catch (ClientException $exception) {
            $this->handleException($exception);
        }
        return null;
    }

    protected function postRequest(string $uri, array $data)
    {
        return post($uri, $data);
    }

    protected function getRequest(string $uri)
    {
        return get($uri);
    }

    /**
     * @param ClientException $exception
     * @throws UndefinededException
     */
    protected function handleException(ClientException $exception) {
        $body = $exception->getResponse()->getBody()->getContents();
        $data = json_decode($body);

        if(!isset($data->ok) && $data->ok === false)
            throw $exception;

        throw new UndefinededException($data->description);
    }
}
