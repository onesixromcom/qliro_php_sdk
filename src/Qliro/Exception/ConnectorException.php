<?php

declare(strict_types=1);

namespace Qliro\Exception;

/**
 * ConnectorException is used to represent a API error response.
 */
class ConnectorException extends \RuntimeException
{
    /**
     * API response error code.
     *
     * @var string
     */
    protected string $errorCode;

    /**
     * API response error reference ID.
     *
     * @var string
     */
    protected string $errorReference;

    /**
     * Constructs a connector exception instance.
     *
     * @param array<string, string> $data Error data
     * @param int                   $code HTTP status code
     */
    public function __construct(array $data, int $code = 0)
    {
        $data = self::setDefaultData($data);

        $message = "{$data['ErrorCode']}: {$data['ErrorMessage']} (#{$data['ErrorReference']})";

        parent::__construct($message, $code);

        $this->errorCode = $data['ErrorCode'];
        $this->errorReference = $data['ErrorReference'];
    }

    /**
     * Gets the API error code for this exception.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Gets the API error reference ID for this exception.
     *
     * @return string
     */
    public function getErrorReference(): string
    {
        return $this->errorReference;
    }

    /**
     * Default data.
     *
     * @param array<string, string> $data
     *
     * @return array<string, string>
     */
    private static function setDefaultData(array $data): array
    {
        $defaults = [
            'ErrorCode' => 'UNDEFINED',
            'ErrorMessage' => 'UNDEFINED',
            'ErrorReference' => 'UNDEFINED',
        ];

        foreach ($defaults as $field => $default) {
            if (!isset($data[$field])) {
                $data[$field] = $default;
            }
        }

        return $data;
    }
}
