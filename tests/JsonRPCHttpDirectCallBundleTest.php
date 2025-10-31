<?php

declare(strict_types=1);

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRPCHttpDirectCallBundle::class)]
#[RunTestsInSeparateProcesses]
final class JsonRPCHttpDirectCallBundleTest extends AbstractBundleTestCase
{
    public function testBundleHasName(): void
    {
        $className = self::getBundleClass();
        $this->assertIsString($className);
        /** @var Bundle $bundle */
        $bundle = new $className();
        $this->assertNotEmpty($bundle->getName());
    }
}
