<?php

declare(strict_types=1);

namespace Qliro;

use GuzzleHttp\Exception\RequestException;
use Qliro\Exception\ConnectorException;
use Qliro\Transport\ConnectorInterface;
use Qliro\Transport\ResponseValidator;

/**
 * Abstract resource class.
 *
 * @extends \ArrayObject<string, mixed>
 */
abstract class Resource extends \ArrayObject
{
    /**
     * Id property field name.
     */
    protected const ID_FIELD = 'id';

    /**
     * Path to the resource endpoint.
     *
     * @var string
     */
    public static $path;

    /**
     * HTTP transport connector instance.
     *
     * @var \Qliro\Transport\ConnectorInterface
     */
    protected $connector;

    /**
     * Url to the resource.
     *
     * @var string
     */
    protected $url;

    /**
     * Constructs a resource instance.
     *
     * @param \Qliro\Transport\ConnectorInterface $connector HTTP transport instance.
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Gets the resource id.
     *
     * @return mixed
     */
    public function getId(): mixed
    {
        return isset($this[static::ID_FIELD]) ? $this[static::ID_FIELD] : null;
    }

    /**
     * Gets the resource location.
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->url;
    }

    /**
     * Sets the resource location.
     *
     * @param string $url Url to the resource
     *
     * @return static
     */
    public function setLocation(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Overrides: Stores the ID KEY field in order to restore it after exchanging the array without
     * the ID field.
     *
     * @param array<string, mixed> $array Data to be exchanged
     *
     * @return array<string, mixed>
     */
    public function exchangeArray($array): array
    {
        $id = $this->getId();

        parent::exchangeArray($array);

        if ($this->getId() === null && $id !== null) {
            $this->setId($id);
        }

        return (array) $this;
    }

    /**
     * @param string[]             $requirements
     * @param array<string, mixed> $data
     */
    protected function validateData(array $requirements, array $data): void
    {
        $dataKeys = array_keys($data);
        foreach ($requirements as $key) {
            if (!in_array($key, $dataKeys)) {
                throw new \RuntimeException("Request data is missing a {$key} parameter.");
            }
        }
    }

    /**
     * Sets new ID KEY field.
     *
     * @param mixed $id ID field
     *
     * @return static
     */
    protected function setId(mixed $id): static
    {
        $this[static::ID_FIELD] = $id;

        return $this;
    }

    /**
     * Sends a HTTP request to the specified url.
     *
     * @param string                         $method  HTTP method, e.g. 'GET'
     * @param string                         $url     Request destination
     * @param array<string, string|string[]> $headers
     * @param array<string, mixed>           $body
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return ResponseValidator
     */
    protected function request(string $method, string $url, array $headers = [], array $body = []): ResponseValidator
    {
        switch ($method) {
            case 'GET':
                $response = $this->connector->get($url, $headers);
                break;
            case 'POST':
                $response = $this->connector->post($url, $body, $headers);
                break;
            case 'PUT':
                $response = $this->connector->put($url, $body, $headers);
                break;
            case 'DELETE':
                $response = $this->connector->delete($url, $body, $headers);
                break;
            case 'PATCH':
                $response = $this->connector->patch($url, $body, $headers);
                break;
            default:
                throw new \RuntimeException('Unknown request method ' . $method);
        }

        $location = $response->getLocation();
        if (!empty($location)) {
            $this->setLocation($location);
        }

        return new ResponseValidator($response);
    }

    /**
     * Sends a HTTP GET request to the specified url.
     *
     * @param string               $url         Request destination
     * @param array<string, mixed> $queryParams Query string parameters
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return ResponseValidator
     */
    protected function get(string $url, array $queryParams = []): ResponseValidator
    {
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $this->request('GET', $url);
    }

    /**
     * Sends a HTTP DELETE request to the specified url.
     *
     * @param string $url  Request destination
     * @param array<string, mixed> $data Data to be JSON encoded
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return ResponseValidator
     */
    protected function delete(string $url, array $data = []): ResponseValidator
    {
        return $this->request(
            'DELETE',
            $url,
            ['Content-Type' => 'application/json'],
            $data
        );
    }

    /**
     * Sends a HTTP PATCH request to the specified url.
     *
     * @param string $url  Request destination
     * @param array<string, mixed> $data Data to be JSON encoded
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return ResponseValidator
     */
    protected function patch(string $url, array $data = []): ResponseValidator
    {
        return $this->request(
            'PATCH',
            $url,
            ['Content-Type' => 'application/json'],
            $data
        );
    }

    /**
     * Sends a HTTP PUT request to the specified url.
     *
     * @param string $url  Request destination
     * @param array<string, mixed> $data Data to be JSON encoded
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return ResponseValidator
     */
    protected function put(string $url, array $data = []): ResponseValidator
    {
        return $this->request(
            'PUT',
            $url,
            ['Content-Type' => 'application/json'],
            $data
        );
    }

    /**
     * Sends a HTTP POST request to the specified url.
     *
     * @param string $url  Request destination
     * @param array<string, mixed> $data Data to be JSON encoded
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return ResponseValidator
     */
    protected function post(string $url, array $data = []): ResponseValidator
    {
        return $this->request(
            'POST',
            $url,
            ['Content-Type' => 'application/json'],
            $data
        );
    }
}
