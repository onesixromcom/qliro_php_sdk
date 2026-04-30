<?php

declare(strict_types=1);

namespace Qliro\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleConnector implements ConnectorInterface
{
    /**
     * HTTP transport client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * API key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * API secret.
     *
     * @var string
     */
    protected $apiSecret;

    /**
     * HTTP user agent.
     *
     * @var mixed
     */
    protected $userAgent;

    /**
     * Constructs a connector instance.
     *
     * @param string $apiKey    API key
     * @param string $apiSecret API secret
     * @param bool   $isTest    Flag for prod/test env.
     * @param mixed  $userAgent HTTP user agent to identify the client
     */
    public function __construct(
        string $apiKey,
        string $apiSecret,
        bool $isTest = false,
        mixed $userAgent = null
    ) {
        $this->client = new Client(['base_uri' => $isTest ? self::TEST_BASE_URL : self::BASE_URL]);
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        if ($userAgent === null) {
            $class = new \ReflectionClass($this->client);
            $version = $class->hasConstant('MAJOR_VERSION') ? $class->getConstant('MAJOR_VERSION') :
                $class->getConstant('VERSION');
            $userAgent = 'Guzzle/' . $version;
        }
        $this->userAgent = $userAgent;
    }

    /**
     * Sends HTTP GET request to specified path.
     *
     * @param string                    $path    URL path.
     * @param array<string, string|string[]> $headers HTTP request headers
     *
     * @throws \GuzzleHttp\Exception\RequestException if HTTP transport failed to execute a call
     *
     * @return ApiResponse Processed response
     */
    public function get(string $path, array $headers = []): ApiResponse
    {
        $request = $this->createRequest($path, 'GET', $headers);
        $response = $this->send($request);

        return $this->getApiResponse($response);
    }

    /**
     * Sends HTTP POST request to specified path.
     *
     * @param string                         $path    URL path.
     * @param array<string, mixed>           $data    Data to be sent to API server in a payload.
     * @param array<string, string|string[]> $headers HTTP request headers
     *
     * @throws \GuzzleHttp\Exception\RequestException if HTTP transport failed to execute a call
     *
     * @return ApiResponse Processed response
     */
    public function post(string $path, array $data = [], array $headers = []): ApiResponse
    {
        $request = $this->createRequest($path, 'POST', $headers, $data);
        $response = $this->send($request);

        return $this->getApiResponse($response);
    }

    /**
     * Sends HTTP PUT request to specified path.
     *
     * @param string                         $path    URL path.
     * @param array<string, mixed>|null      $data    Data to be sent to API server in a payload.
     * @param array<string, string|string[]> $headers HTTP request headers
     *
     * @throws \GuzzleHttp\Exception\RequestException if HTTP transport failed to execute a call
     *
     * @return ApiResponse Processed response
     */
    public function put(string $path, array|null $data = null, array $headers = []): ApiResponse
    {
        $request = $this->createRequest($path, 'PUT', $headers, $data);
        $response = $this->send($request);

        return $this->getApiResponse($response);
    }

    /**
     * Sends HTTP PATCH request to specified path.
     *
     * @param string                         $path    URL path.
     * @param array<string, mixed>|null      $data    Data to be sent to API server in a payload.
     * @param array<string, string|string[]> $headers HTTP request headers
     *
     * @throws \GuzzleHttp\Exception\RequestException if HTTP transport failed to execute a call
     *
     * @return ApiResponse Processed response
     */
    public function patch(string $path, array|null $data = null, array $headers = []): ApiResponse
    {
        $request = $this->createRequest($path, 'PATCH', $headers, $data);
        $response = $this->send($request);

        return $this->getApiResponse($response);
    }

    /**
     * Sends HTTP DELETE request to specified path.
     *
     * @param string                         $path    URL path.
     * @param array<string, mixed>|null      $data    Data to be sent to API server in a payload.
     * @param array<string, string|string[]> $headers HTTP request headers
     *
     * @throws \GuzzleHttp\Exception\RequestException if HTTP transport failed to execute a call
     *
     * @return ApiResponse Processed response
     */
    public function delete(string $path, array|null $data = null, array $headers = []): ApiResponse
    {
        $request = $this->createRequest($path, 'DELETE', $headers, $data);
        $response = $this->send($request);

        return $this->getApiResponse($response);
    }

    /**
     * Gets the HTTP transport client.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Gets the user agent.
     *
     * @return mixed
     */
    public function getUserAgent(): mixed
    {
        return $this->userAgent;
    }

    /**
     * Converts ResponseInterface to ApiResponse.
     *
     * @param ResponseInterface $response ResponseInterface instance
     *
     * @return ApiResponse
     */
    protected function getApiResponse(ResponseInterface $response): ApiResponse
    {
        return new ApiResponse(
            $response->getStatusCode(),
            $response->getBody()->getContents(),
            $response->getHeaders()
        );
    }

    /**
     * Creates a request object.
     *
     * @param string                         $url     URL
     * @param string                         $method  HTTP method
     * @param array<string, string|string[]> $headers HTTP headers
     * @param array<string, mixed>|null      $body    Request body
     *
     * @return RequestInterface
     */
    private function createRequest(string $url, string $method = 'GET', array $headers = [], array|null $body = []): RequestInterface
    {
        $headers = array_merge($headers, ['User-Agent' => strval($this->userAgent)]);
        if (!empty($body)) {
            $body = array_merge($body, ['MerchantApiKey' => $this->apiKey]);
        }

        return new Request($method, $url, $headers, empty($body) ? null : json_encode($body));
    }

    /**
     * Sends the request.
     *
     * @param RequestInterface     $request Request to send
     * @param array<string, mixed> $options Request options
     *
     * @throws \RuntimeException When an error is encountered
     *
     * @return ResponseInterface
     */
    private function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $token = base64_encode(hex2bin(hash('sha256', (string) $request->getBody() . $this->apiSecret)));
        $options['headers']['Authorization'] = 'Qliro ' . $token;
        $options['http_errors'] = false;

        try {
            return $this->client->send($request, $options);
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
