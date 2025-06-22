<?php

namespace Tourze\JsonRPCHttpDirectCallBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectCallController;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectPostController;

class JsonRPCHttpDirectCallBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(DirectCallController::class))->getFileName());
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(DirectPostController::class))->getFileName());
    }
}
