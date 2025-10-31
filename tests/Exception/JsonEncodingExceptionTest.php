<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPCHttpDirectCallBundle\Exception\JsonEncodingException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonEncodingException::class)]
final class JsonEncodingExceptionTest extends AbstractExceptionTestCase
{
    public function testForContent(): void
    {
        $exception = JsonEncodingException::forContent();

        $this->assertInstanceOf(JsonEncodingException::class, $exception);
        $this->assertEquals('Failed to encode JSON content', $exception->getMessage());
    }

    public function testForResponse(): void
    {
        $exception = JsonEncodingException::forResponse();

        $this->assertInstanceOf(JsonEncodingException::class, $exception);
        $this->assertEquals('Failed to encode JSON response', $exception->getMessage());
    }

    public function testForRequest(): void
    {
        $exception = JsonEncodingException::forRequest();

        $this->assertInstanceOf(JsonEncodingException::class, $exception);
        $this->assertEquals('Failed to encode request content', $exception->getMessage());
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = JsonEncodingException::forContent();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
