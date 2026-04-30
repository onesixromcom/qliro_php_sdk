<?php

declare(strict_types=1);

namespace Qliro\Transport;

/**
 * HTTP transport connector interface used to authenticate and make HTTP requests
 * against the Qliro APIs.
 *
 * The HTTP communication is handled by
 * {@link https://docs.guzzlephp.org/en/stable/index.html Guzzle}.
 */
interface ConnectorInterface
{
    /**
     * API base URL.
     */
    const BASE_URL = 'https://payments.qit.nu/';

    /**
     * Testing API base URL.
     */
    const TEST_BASE_URL = 'https://pago.qit.nu/';

    /**
     * Sends HTTP GET request to specified path.
     *
     * @param string                         $path    URL path.
     * @param array<string, string|string[]> $headers HTTP request headers
     *
     * @throws \GuzzleHttp\Exception\RequestException if HTTP transport failed to execute a call
     *
     * @return ApiResponse Processed response
     */
    public function get(string $path, array $headers = []): ApiResponse;

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
    public function post(string $path, array $data = [], array $headers = []): ApiResponse;

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
    public function put(string $path, array|null $data = null, array $headers = []): ApiResponse;

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
    public function patch(string $path, array|null $data = null, array $headers = []): ApiResponse;

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
    public function delete(string $path, array|null $data = null, array $headers = []): ApiResponse;
}
