<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Exception;

final class UnexpectedControllerException extends \InvalidArgumentException
{
    public static function create(string $controller): self
    {
        return new self("Unexpected controller: {$controller}");
    }
}
