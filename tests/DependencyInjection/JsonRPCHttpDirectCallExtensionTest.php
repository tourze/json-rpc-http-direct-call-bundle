<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\JsonRPCHttpDirectCallBundle\DependencyInjection\JsonRPCHttpDirectCallExtension;

class JsonRPCHttpDirectCallExtensionTest extends TestCase
{
    /**
     * 测试load方法不抛出异常
     */
    public function testLoad_loadsServicesYamlFile(): void
    {
        $extension = new JsonRPCHttpDirectCallExtension();
        $container = new ContainerBuilder();

        // 设置一个空的配置数组
        $configs = [];

        // 测试方法是否可以正常执行不抛出异常
        try {
            $extension->load($configs, $container);
            $this->assertTrue(true, 'Extension load method executed without exceptions');
        } catch (\Exception $e) {
            $this->fail('Extension load method should not throw exceptions: ' . $e->getMessage());
        }

        // 验证服务是否已注册
        $this->assertTrue($container->has('Tourze\JsonRPCHttpDirectCallBundle\Controller\JsonRpcController') ||
            $container->hasDefinition('Tourze\JsonRPCHttpDirectCallBundle\Controller\JsonRpcController'),
            'JsonRpcController service should be registered');
    }
}
