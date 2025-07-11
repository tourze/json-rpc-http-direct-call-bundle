<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPCHttpDirectCallBundle\Exception\UnexpectedControllerException;

class UnexpectedControllerExceptionTest extends TestCase
{
    /**
     * 测试异常类是否继承自 InvalidArgumentException
     */
    public function testExceptionExtendsInvalidArgumentException(): void
    {
        $exception = UnexpectedControllerException::create('TestController');
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    /**
     * 测试create静态方法创建异常
     */
    public function testCreateReturnsExceptionWithCorrectMessage(): void
    {
        $controller = 'TestController';
        $exception = UnexpectedControllerException::create($controller);

        $this->assertInstanceOf(UnexpectedControllerException::class, $exception);
        $this->assertEquals("Unexpected controller: $controller", $exception->getMessage());
    }

    /**
     * 测试异常消息格式
     */
    public function testExceptionMessageFormat(): void
    {
        $controller = 'App\\Controller\\TestController';
        $exception = UnexpectedControllerException::create($controller);

        $expectedMessage = "Unexpected controller: $controller";
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}