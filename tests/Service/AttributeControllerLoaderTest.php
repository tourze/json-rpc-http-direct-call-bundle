<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\JsonRpcController;
use Tourze\JsonRPCHttpDirectCallBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;
    private MockObject|AttributeRouteControllerLoader $controllerLoader;

    protected function setUp(): void
    {
        $this->loader = new AttributeControllerLoader();

        // 使用反射获取和替换controllerLoader属性
        $this->controllerLoader = $this->createMock(AttributeRouteControllerLoader::class);
        $reflectionProperty = new ReflectionProperty(AttributeControllerLoader::class, 'controllerLoader');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->loader, $this->controllerLoader);
    }

    /**
     * 测试支持方法总是返回false
     */
    public function testSupports_alwaysReturnsFalse(): void
    {
        $result = $this->loader->supports('any_resource', 'any_type');
        $this->assertFalse($result);
    }

    /**
     * 测试自动加载方法调用控制器加载器的load方法
     */
    public function testAutoload_callsControllerLoaderWithJsonRpcController(): void
    {
        // 创建一个模拟的RouteCollection
        $mockRouteCollection = $this->createMock(RouteCollection::class);

        // 设置预期行为
        $this->controllerLoader->expects($this->once())
            ->method('load')
            ->with(JsonRpcController::class)
            ->willReturn($mockRouteCollection);

        // 执行被测方法
        $result = $this->loader->autoload();

        // 断言结果
        $this->assertSame($mockRouteCollection, $result);
    }

    /**
     * 测试load方法调用autoload方法
     */
    public function testLoad_callsAutoload(): void
    {
        // 创建一个模拟的RouteCollection
        $mockRouteCollection = $this->createMock(RouteCollection::class);

        // 创建一个部分mock，只mock autoload方法
        $partialMock = $this->createPartialMock(AttributeControllerLoader::class, ['autoload']);

        // 设置预期行为
        $partialMock->expects($this->once())
            ->method('autoload')
            ->willReturn($mockRouteCollection);

        // 执行被测方法
        $result = $partialMock->load('any_resource', 'any_type');

        // 断言结果
        $this->assertSame($mockRouteCollection, $result);
    }
}
