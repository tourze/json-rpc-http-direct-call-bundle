<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Exception;

use InvalidArgumentException;

class UnexpectedControllerException extends InvalidArgumentException
{
    public static function create(string $controller): self
    {
        return new self("Unexpected controller: $controller");
    }
}