<?php

declare(strict_types=1);

namespace Qliro\Tests\Transport;

use PHPUnit\Framework\TestCase;
use Qliro\Exception\ConnectorException;
use Qliro\Transport\ApiResponse;
use Qliro\Transport\ResponseValidator;
use RuntimeException;

class ResponseValidatorTest extends TestCase
{
    /**
     * @param array<string, string[]> $headers
     */
    private function makeValidator(int $status, string $body = '', array $headers = []): ResponseValidator
    {
        return new ResponseValidator(new ApiResponse($status, $body, $headers));
    }

    public function testIsSuccessfulReturnsTrueFor2xx(): void
    {
        $this->assertTrue($this->makeValidator(200)->isSuccessful());
        $this->assertTrue($this->makeValidator(201)->isSuccessful());
        $this->assertTrue($this->makeValidator(204)->isSuccessful());
        $this->assertTrue($this->makeValidator(299)->isSuccessful());
    }

    public function testIsSuccessfulReturnsFalseForNon2xx(): void
    {
        $this->assertFalse($this->makeValidator(400)->isSuccessful());
        $this->assertFalse($this->makeValidator(404)->isSuccessful());
        $this->assertFalse($this->makeValidator(500)->isSuccessful());
    }

    public function testStatusPassesOnMatch(): void
    {
        $validator = $this->makeValidator(200);
        $this->assertSame($validator, $validator->status('200'));
    }

    public function testStatusPassesOnArrayMatch(): void
    {
        $validator = $this->makeValidator(201);
        $this->assertSame($validator, $validator->status(['200', '201']));
    }

    public function testStatusThrowsOnMismatch(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/404/');

        $this->makeValidator(404)->status('200');
    }

    public function testStatusThrowsOnArrayMismatch(): void
    {
        $this->expectException(RuntimeException::class);

        $this->makeValidator(500)->status(['200', '201']);
    }

    public function testContentTypePassesOnMatch(): void
    {
        $validator = $this->makeValidator(200, '', ['Content-Type' => ['application/json']]);
        $this->assertSame($validator, $validator->contentType('application/json'));
    }

    public function testContentTypePassesWithCharset(): void
    {
        $validator = $this->makeValidator(200, '', ['Content-Type' => ['application/json; charset=utf-8']]);
        $this->assertSame($validator, $validator->contentType('application/json'));
    }

    public function testContentTypeThrowsWhenMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Content-Type/');

        $this->makeValidator(200)->contentType('application/json');
    }

    public function testContentTypeThrowsOnMismatch(): void
    {
        $this->expectException(RuntimeException::class);

        $this->makeValidator(200, '', ['Content-Type' => ['text/plain']])->contentType('application/json');
    }

    public function testGetJsonDecodesBody(): void
    {
        $validator = $this->makeValidator(200, '{"OrderId":42}');

        $this->assertSame(['OrderId' => 42], $validator->getJson());
    }

    public function testGetBodyReturnsRawBody(): void
    {
        $validator = $this->makeValidator(200, 'raw body');

        $this->assertSame('raw body', $validator->getBody());
    }

    public function testExpectSuccessfulPassesOn2xx(): void
    {
        $validator = $this->makeValidator(200);
        $this->assertSame($validator, $validator->expectSuccessful());
    }

    public function testExpectSuccessfulThrowsConnectorExceptionWhenErrorCodePresent(): void
    {
        $this->expectException(ConnectorException::class);

        $body = json_encode([
            'ErrorCode' => 'ORDER_NOT_FOUND',
            'ErrorMessage' => 'Not found',
            'ErrorReference' => 'ref-1',
        ]);

        $this->makeValidator(404, $body)->expectSuccessful();
    }

    public function testExpectSuccessfulThrowsRuntimeExceptionForGenericError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/500/');

        $this->makeValidator(500, 'Internal Server Error')->expectSuccessful();
    }

    public function testGetResponseReturnsApiResponse(): void
    {
        $response = new ApiResponse(200, '');
        $validator = new ResponseValidator($response);

        $this->assertSame($response, $validator->getResponse());
    }
}
