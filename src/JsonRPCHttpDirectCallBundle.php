<?php

namespace Tourze\JsonRPCHttpDirectCallBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BacktraceHelper\Backtrace;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\JsonRpcController;

class JsonRPCHttpDirectCallBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();
        Backtrace::addProdIgnoreFiles((new \ReflectionClass(JsonRpcController::class))->getFileName());
    }
}
