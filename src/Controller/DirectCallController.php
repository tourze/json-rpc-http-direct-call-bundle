<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Controller;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPC\Core\Serialization\JsonRpcResponseNormalizer;
use Tourze\JsonRPCEncryptBundle\Service\Encryptor;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint as SDKJsonRpcEndpoint;
use Tourze\JsonRPCHttpDirectCallBundle\Exception\JsonEncodingException;

#[WithMonologChannel(channel: 'json_rpc_http_direct_call')]
final class DirectCallController extends AbstractController
{
    public function __construct(
        private readonly SDKJsonRpcEndpoint $sdkEndpoint,
        private readonly LoggerInterface $logger,
        private readonly Encryptor $encryptor,
        private readonly JsonRpcResponseNormalizer $responseNormalizer,
    ) {
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
        $content = $this->processRequestContent($request);
        $json = $this->parseJsonContent($content);
        $id = $this->generateRequestId($request, $prefix);

        $payload = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $json,
            'id' => $id,
        ];

        return $this->executeJsonRpcCall($payload, $request, $id);
    }

    private function processRequestContent(Request $request): string
    {
        $content = $request->getContent();
        if (false === $content) {
            return '';
        }

        if ($this->encryptor->shouldEncrypt($request)) {
            $decrypted = $this->encryptor->decryptByRequest($request, $content);
            if (false === $decrypted) {
                return '';
            }
            $this->logger->debug('解密JsonRPC提交数据', [
                'raw' => $content,
                'dec' => $decrypted,
            ]);

            return $decrypted;
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJsonContent(string $content): array
    {
        if ('' === $content) {
            return [];
        }

        $json = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->logger->error('JSON数据反序列化失败', ['content' => $content, 'error' => json_last_error_msg()]);

            return [];
        }

        if (!is_array($json)) {
            $this->logger->error('JSON数据不是数组格式', ['content' => $content, 'decoded' => $json]);

            return [];
        }

        // 确保数组键都是字符串类型
        $result = [];
        foreach ($json as $key => $value) {
            $result[(string) $key] = $value;
        }

        return $result;
    }

    private function generateRequestId(Request $request, string $prefix): string
    {
        $id = $request->headers->get('request-id');
        if (null === $id || '' === $id) {
            return $prefix . Uuid::v4()->toRfc4122();
        }

        return htmlentities($id);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function executeJsonRpcCall(array $payload, Request $request, string $id): Response
    {
        try {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            $jsonContent = json_encode($payload);
            if (false === $jsonContent) {
                throw JsonEncodingException::forContent();
            }

            $content = $this->sdkEndpoint->index($jsonContent, $request);
            if ($this->encryptor->shouldEncrypt($request) && str_starts_with($content, '{"jsonrpc"')) {
                $content = $this->encryptor->encryptByRequest($request, $content);
            }
            $response->setContent($content);

            return $response;
        } catch (\Throwable $exception) {
            return $this->createErrorResponse($exception, $id);
        }
    }

    private function createErrorResponse(\Throwable $exception, string $id): JsonResponse
    {
        $this->logger->error('发生未知的JSON-RPC异常', [
            'exception' => $exception,
        ]);

        $jsonRpcResponse = new JsonRpcResponse();
        $jsonRpcResponse->setId($id);
        $jsonRpcResponse->setError(new JsonRpcInternalErrorException($exception));

        return new JsonResponse($this->responseNormalizer->normalize($jsonRpcResponse));
    }
}
