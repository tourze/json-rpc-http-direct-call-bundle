<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\JsonRPCHttpDirectCallBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $loader = self::getContainer()->get(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
        $this->loader = $loader;
    }

    /**
     * 测试支持方法总是返回false
     */
    public function testSupportsAlwaysReturnsFalse(): void
    {
        $result = $this->loader->supports('any_resource', 'any_type');
        $this->assertFalse($result);
    }

    /**
     * 测试自动加载方法返回路由集合
     */
    public function testAutoloadReturnsRouteCollection(): void
    {
        $result = $this->loader->autoload();
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    /**
     * 测试load方法调用autoload方法
     */
    public function testLoadCallsAutoload(): void
    {
        $result1 = $this->loader->autoload();
        $result2 = $this->loader->load('any_resource', 'any_type');

        $this->assertInstanceOf(RouteCollection::class, $result1);
        $this->assertInstanceOf(RouteCollection::class, $result2);
    }
}
