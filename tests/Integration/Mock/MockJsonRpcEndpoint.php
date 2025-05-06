<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Integration\Mock;

use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcCallSerializer;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;

class MockJsonRpcEndpoint extends JsonRpcEndpoint
{
    /**
     * 创建一个简化的构造函数，避免依赖SerializerInterface等
     */
    public function __construct(JsonRpcCallSerializer $jsonRpcCallSerializer)
    {
        // 由于我们重写了index方法，实际上不需要调用父构造函数
        // 但为了避免PHP警告，我们还是传入所需的参数
    }

    /**
     * 重写index方法，返回固定响应
     */
    public function index(string $content, ?Request $request = null): string
    {
        $data = json_decode($content, true);

        // 返回一个固定的JSON-RPC响应
        return json_encode([
            'jsonrpc' => '2.0',
            'result' => 'mocked_result',
            'id' => $data['id'] ?? null,
        ]);
    }
}
