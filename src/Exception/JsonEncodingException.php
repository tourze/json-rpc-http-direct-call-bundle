<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Exception;

class JsonEncodingException extends \RuntimeException
{
    public static function forContent(): self
    {
        return new self('Failed to encode JSON content');
    }

    public static function forResponse(): self
    {
        return new self('Failed to encode JSON response');
    }

    public static function forRequest(): self
    {
        return new self('Failed to encode request content');
    }
}
