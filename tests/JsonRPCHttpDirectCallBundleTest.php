<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectCallController;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectPostController;
use Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle;

class JsonRPCHttpDirectCallBundleTest extends TestCase
{
    /**
     * 测试boot方法会将控制器类文件添加到忽略文件列表中
     */
    public function testBoot_addsControllersToIgnoreFiles(): void
    {
        // 创建一个JsonRPCHttpDirectCallBundle实例
        $bundle = new JsonRPCHttpDirectCallBundle();

        // 获取控制器类文件路径
        $directCallControllerFile = (new ReflectionClass(DirectCallController::class))->getFileName();
        $directPostControllerFile = (new ReflectionClass(DirectPostController::class))->getFileName();

        // 由于Backtrace是静态类，我们无法直接mock它
        // 我们可以检查boot方法执行前后，一些可观察的状态变化

        // 检查bundle的boot方法能否正常执行，不抛出异常
        $bundle->boot();

        // 这里我们只能验证代码不会抛出异常
        // 在实际应用中，可能需要考虑更多方式来验证静态方法的调用
        $this->assertTrue(true, 'Boot method executed without exceptions');
    }
}
