<?php

namespace Tourze\JsonRPCHttpDirectCallBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\JsonRPCEncryptBundle\JsonRPCEncryptBundle;
use Tourze\JsonRPCEndpointBundle\JsonRPCEndpointBundle;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectCallController;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectPostController;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

class JsonRPCHttpDirectCallBundle extends Bundle implements BundleDependencyInterface
{
    public function boot(): void
    {
        parent::boot();
        $directCallFileName = (new \ReflectionClass(DirectCallController::class))->getFileName();
        if (false !== $directCallFileName) {
            Backtrace::addProdIgnoreFiles($directCallFileName);
        }

        $directPostFileName = (new \ReflectionClass(DirectPostController::class))->getFileName();
        if (false !== $directPostFileName) {
            Backtrace::addProdIgnoreFiles($directPostFileName);
        }
    }

    public static function getBundleDependencies(): array
    {
        return [
            JsonRPCEndpointBundle::class => ['all' => true],
            JsonRPCEncryptBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
        ];
    }
}
