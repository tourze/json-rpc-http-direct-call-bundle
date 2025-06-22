<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEncryptBundle\Service\Encryptor;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcResponseNormalizer;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint as SDKJsonRpcEndpoint;

class DirectCallController extends AbstractController
{
    public function __construct(
        private readonly SDKJsonRpcEndpoint $sdkEndpoint,
        private readonly LoggerInterface $logger,
        private readonly Encryptor $encryptor,
        private readonly JsonRpcResponseNormalizer $responseNormalizer,
    )
    {
    }

    /**
     * 暂时使用 aspx 这个后缀来迷惑人
     * 因为这个入口会暴露实际接口意图了，所以这里就不做加密了
     *
     * @param string $prefix 只是用于我们识别是哪个服务，方便我们做流量分发，目前还没用处
     * @param string $method 方法名
     */
    #[Route(path: '/json-rpc/{prefix}/{method}.aspx', name: 'rpc_http_server_caller', methods: ['POST'])]
    #[Route(path: '/cp/json-rpc/{method}.aspx', name: 'json_rpc_cp_caller', defaults: ['prefix' => ''], methods: ['POST'])]
    public function __invoke(string $prefix, string $method, Request $request): Response
    {
        $content = $request->getContent();
        // 如果有加密，我们就在这里解密算了
        if ($this->encryptor->shouldEncrypt($request)) {
            $d = $this->encryptor->decryptByRequest($request, $content);
            $this->logger->debug('解密JsonRPC提交数据', [
                'raw' => $content,
                'dec' => $d,
            ]);
            $content = $d;
        }

        $json = empty($content) ? [] : json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('JSON数据反序列化失败', ['content' => $content, 'error' => json_last_error_msg()]);
            $json = [];
        }

        $id = $request->headers->get('request-id');
        if ($id === null || $id === '') {
            $id = $prefix . Uuid::v4()->toRfc4122();
        } else {
            $id = htmlentities($id); // 防止有东西乱入
        }

        // 根据入参，我们自己拼凑一个JSON Payload
        $content = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $json,
            'id' => $id,
        ];

        try {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            $content = $this->sdkEndpoint->index(json_encode($content), $request);
            if ($this->encryptor->shouldEncrypt($request) && str_starts_with($content, '{"jsonrpc"')) {
                $content = $this->encryptor->encryptByRequest($request, $content);
            }
            $response->setContent($content);
        } catch (\Throwable $exception) {
            $this->logger->error('发生未知的JSON-RPC异常', [
                'exception' => $exception,
            ]);

            $j = new JsonRpcResponse();
            $j->setId($id);
            $j->setError(new JsonRpcException(-1, $exception->getMessage(), previous: $exception));
            $response = new JsonResponse($this->responseNormalizer->normalize($j));
        }

        return $response;
    }
}