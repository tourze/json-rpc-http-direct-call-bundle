<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Integration\Mock;

use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPCEncryptBundle\Service\Encryptor;

class MockEncryptor extends Encryptor
{
    /**
     * 判断请求是否需要加密
     */
    public function shouldEncrypt(Request $request): bool
    {
        return str_contains($request->getPathInfo(), '/encrypt/');
    }

    /**
     * 解密请求内容
     */
    public function decryptByRequest(Request $request, string $content): string
    {
        // 简单模拟解密过程，将加密标记替换为实际内容
        return str_replace('ENCRYPTED:', '', $content);
    }

    /**
     * 加密响应内容
     */
    public function encryptByRequest(Request $request, string $content): string
    {
        // 简单模拟加密过程，添加加密标记
        return 'ENCRYPTED:' . $content;
    }
}
