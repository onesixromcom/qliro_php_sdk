<?php

declare(strict_types=1);

namespace Qliro\Transport;

use Qliro\Exception\ConnectorException;

/**
 * HTTP response validator helper class.
 */
class ResponseValidator
{
    /**
     * HTTP response to validate against.
     *
     * @var ApiResponse
     */
    protected $response;

    /**
     * Constructs a response validator instance.
     *
     * @param ApiResponse $response Response to validate
     */
    public function __construct(ApiResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Gets the response object.
     *
     * @return ApiResponse
     */
    public function getResponse(): ApiResponse
    {
        return $this->response;
    }

    /**
     * Asserts the HTTP response status code.
     *
     * @param string|string[] $status Expected status code(s)
     *
     * @throws \RuntimeException If status code does not match
     *
     * @return static
     */
    public function status(string|array $status): static
    {
        $httpStatus = (string) $this->response->getStatus();
        if (is_array($status) && !in_array($httpStatus, $status)) {
            throw new \RuntimeException(
                "Unexpected response status code: {$httpStatus}"
            );
        }

        if (is_string($status) && $httpStatus !== $status) {
            throw new \RuntimeException(
                "Unexpected response status code: {$httpStatus}"
            );
        }

        return $this;
    }

    /**
     * Asserts the Content-Type header. Checks partial matching.
     * Validation PASSES in the following cases:
     *      Content-Type: application/json
     *      $mediaType = 'application/json'
     *
     *      Content-Type: application/json; charset=utf-8
     *      $mediaType = 'application/json'
     *
     * Validation FAILS in the following cases:
     *      Content-Type: plain/text
     *      $mediaType = 'application/json'
     *
     *      Content-Type: application/json; charset=utf-8
     *      $mediaType = 'application/json; charset=cp-1251'
     *
     * @param string $mediaType Expected media type. RegExp rules can be used.
     *
     * @throws \RuntimeException If Content-Type header is missing
     * @throws \RuntimeException If Content-Type header does not match
     *
     * @return static
     */
    public function contentType(string $mediaType): static
    {
        $contentType = $this->response->getHeader('Content-Type');
        if (empty($contentType)) {
            throw new \RuntimeException('Response is missing a Content-Type header');
        }
        $mediaFound = false;
        foreach ($contentType as $type) {
            if (preg_match('#' . $mediaType . '#', $type)) {
                $mediaFound = true;
                break;
            }
        }

        if (!$mediaFound) {
            throw new \RuntimeException(
                'Unexpected Content-Type header received: '
                    . implode(',', $contentType) . '. Expected: ' . $mediaType
            );
        }

        return $this;
    }

    /**
     * Gets the decoded JSON response.
     *
     * @return mixed
     */
    public function getJson(): mixed
    {
        return \json_decode($this->response->getBody(), true);
    }

    /**
     * Gets response body.
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->response->getBody();
    }

    /**
     * Gets the Location header.
     *
     * @throws \RuntimeException If the Location header is missing
     *
     * @return string
     */
    public function getLocation(): string
    {
        $location = $this->response->getHeader('Location');
        if (empty($location)) {
            throw new \RuntimeException('Response is missing a Location header');
        }

        return $location[0];
    }

    /**
     * Asserts and analyzes the response.
     *
     * @throws \RuntimeException if response has non-2xx HTTP code and body is not parsable
     *
     * @return static
     */
    public function expectSuccessful(): static
    {
        if (!$this->isSuccessful()) {
            $data = json_decode($this->response->getBody(), true);
            if (is_array($data) && array_key_exists('ErrorCode', $data)) {
                throw new ConnectorException($data, $this->response->getStatus());
            }

            throw new \RuntimeException(
                'Unexpected response HTTP status ' . $this->response->getStatus() .
                    '. Expected HTTP status should be in 2xx range.',
                $this->response->getStatus()
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        $status = $this->response->getStatus();

        return $status >= 200 && $status < 300;
    }
}
