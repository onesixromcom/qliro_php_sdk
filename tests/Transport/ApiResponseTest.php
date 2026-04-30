<?php

declare(strict_types=1);

namespace Qliro\Tests\Transport;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Qliro\Transport\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $response = new ApiResponse(200, '{"ok":true}', ['Content-Type' => ['application/json']]);

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('{"ok":true}', $response->getBody());
    }

    public function testGetHeaderIsCaseInsensitive(): void
    {
        $response = new ApiResponse(200, '', ['Content-Type' => ['application/json']]);

        $this->assertSame(['application/json'], $response->getHeader('content-type'));
        $this->assertSame(['application/json'], $response->getHeader('CONTENT-TYPE'));
    }

    public function testGetHeaderReturnsEmptyArrayWhenMissing(): void
    {
        $response = new ApiResponse(200, '');

        $this->assertSame([], $response->getHeader('X-Missing'));
    }

    public function testGetLocationReturnsFirstValue(): void
    {
        $response = new ApiResponse(201, '', ['Location' => ['https://payments.qit.nu/orders/42']]);

        $this->assertSame('https://payments.qit.nu/orders/42', $response->getLocation());
    }

    public function testGetLocationReturnsNullWhenAbsent(): void
    {
        $response = new ApiResponse(200, '');

        $this->assertNull($response->getLocation());
    }

    public function testDuplicateHeadersAreMerged(): void
    {
        $response = new ApiResponse(200, '', [
            'X-Custom' => ['first'],
        ]);
        $response->setHeaders(['X-Custom' => ['second']]);

        $this->assertSame(['second'], $response->getHeader('X-Custom'));
    }

    public function testSetHeadersRejectsInvalidHeaderName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new ApiResponse();
        $response->setHeaders(['bad header name' => ['value']]);
    }

    public function testSetHeadersRejectsEmptyValueArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new ApiResponse();
        $response->setHeaders(['X-Custom' => []]);
    }

    public function testSetStatus(): void
    {
        $response = new ApiResponse();
        $response->setStatus(404);

        $this->assertSame(404, $response->getStatus());
    }

    public function testSetBody(): void
    {
        $response = new ApiResponse();
        $response->setBody('hello');

        $this->assertSame('hello', $response->getBody());
    }
}
