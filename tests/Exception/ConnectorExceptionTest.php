<?php

declare(strict_types=1);

namespace Qliro\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Qliro\Exception\ConnectorException;

class ConnectorExceptionTest extends TestCase
{
    public function testMessageFormat(): void
    {
        $exception = new ConnectorException([
            'ErrorCode' => 'ORDER_NOT_FOUND',
            'ErrorMessage' => 'The order was not found',
            'ErrorReference' => 'abc-123',
        ]);

        $this->assertSame('ORDER_NOT_FOUND: The order was not found (#abc-123)', $exception->getMessage());
    }

    public function testGetErrorCode(): void
    {
        $exception = new ConnectorException([
            'ErrorCode' => 'INVALID_REQUEST',
            'ErrorMessage' => 'Bad input',
            'ErrorReference' => 'ref-1',
        ]);

        $this->assertSame('INVALID_REQUEST', $exception->getErrorCode());
    }

    public function testGetErrorReference(): void
    {
        $exception = new ConnectorException([
            'ErrorCode' => 'ERR',
            'ErrorMessage' => 'Msg',
            'ErrorReference' => 'ref-xyz',
        ]);

        $this->assertSame('ref-xyz', $exception->getErrorReference());
    }

    public function testHttpStatusCodeIsSet(): void
    {
        $exception = new ConnectorException([
            'ErrorCode' => 'ERR',
            'ErrorMessage' => 'Msg',
            'ErrorReference' => 'ref',
        ], 422);

        $this->assertSame(422, $exception->getCode());
    }

    public function testMissingFieldsFallBackToUndefined(): void
    {
        $exception = new ConnectorException([]);

        $this->assertSame('UNDEFINED', $exception->getErrorCode());
        $this->assertSame('UNDEFINED', $exception->getErrorReference());
        $this->assertStringContainsString('UNDEFINED', $exception->getMessage());
    }

    public function testPartialDataUsesDefaults(): void
    {
        $exception = new ConnectorException(['ErrorCode' => 'PARTIAL']);

        $this->assertSame('PARTIAL', $exception->getErrorCode());
        $this->assertSame('UNDEFINED', $exception->getErrorReference());
    }
}
