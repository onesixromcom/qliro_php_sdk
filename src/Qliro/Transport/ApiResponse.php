<?php

declare(strict_types=1);

namespace Qliro\Transport;

/**
 * General HTTP response instance.
 */
class ApiResponse
{
    /**
     * HTTP response status code.
     *
     * @var int|null
     */
    private ?int $status;

    /**
     * HTTP response headers.
     *
     * @var array<string, string[]>
     */
    private array $headers = [];

    /** @var array<string, string> Map of lowercase header name => original name at registration */
    private array $headerNames = [];

    /**
     * HTTP body binary payload.
     *
     * @var string|null
     */
    private ?string $body = null;

    /**
     * @param array<array-key, string|string[]> $headers
     */
    public function __construct(?int $status = null, ?string $body = null, array $headers = [])
    {
        $this->setStatus($status);
        $this->setBody($body);
        $this->setHeaders($headers);
    }

    /**
     * Sets HTTP status code.
     *
     * @param int|null $status HTTP status
     *
     * @return static
     */
    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets HTTP status code.
     *
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Sets binary body payload.
     *
     * @param string|null $body
     *
     * @return static
     */
    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Gets binary body payload.
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Sets HTTP headers map.
     *
     * @param array<array-key, string|string[]> $headers
     *
     * @return static
     */
    public function setHeaders(array $headers): static
    {
        $this->headerNames = $this->headers = [];
        foreach ($headers as $header => $value) {
            if (is_int($header)) {
                // Numeric array keys are converted to int by PHP but having a header name '123' is not forbidden
                // by the spec and also allowed in withHeader().
                // So we need to cast it to string again for the following assertion to pass.
                $header = (string) $header;
            }
            $this->assertHeader($header);
            $value = $this->normalizeHeaderValue($value);
            $normalized = strtolower($header);
            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }

        return $this;
    }

    /**
     * Sets single HTTP header value.
     *
     * @param string $name
     * @param mixed  $values
     *
     * @return static
     */
    public function setHeader(string $name, mixed $values): static
    {
        $this->headers[$name] = $values;

        return $this;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array<string, string[]> Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader(string $name): array
    {
        $header = strtolower($name);

        if (!isset($this->headerNames[$header])) {
            return [];
        }

        $header = $this->headerNames[$header];

        return $this->headers[$header];
    }

    /**
     * Gets the Location header helper.
     *
     * @return string|null Location if exists, null otherwise.
     */
    public function getLocation(): ?string
    {
        $header = $this->getHeader('Location');

        return empty($header) ? null : $header[0];
    }

    /**
     * @param mixed $value
     *
     * @return string[]
     */
    private function normalizeHeaderValue(mixed $value): array
    {
        if (!is_array($value)) {
            return $this->trimHeaderValues([$value]);
        }

        if (count($value) === 0) {
            throw new \InvalidArgumentException('Header value can not be an empty array.');
        }

        return $this->trimHeaderValues($value);
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param array<int, mixed> $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @link https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values): array
    {
        return array_map(function ($value) {
            if (!is_scalar($value) && null !== $value) {
                throw new \InvalidArgumentException(sprintf(
                    'Header value must be scalar or null but %s provided.',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            return trim((string) $value, " \t");
        }, array_values($values));
    }

    /**
     * @link https://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @param mixed $header
     */
    private function assertHeader(mixed $header): void
    {
        if (!is_string($header)) {
            throw new \InvalidArgumentException(sprintf(
                'Header name must be a string but %s provided.',
                is_object($header) ? get_class($header) : gettype($header)
            ));
        }

        if (! preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $header)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '"%s" is not valid header name',
                    $header
                )
            );
        }
    }
}
