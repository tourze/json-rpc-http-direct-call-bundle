<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPCEncryptBundle\Service\Encryptor;
use Tourze\JsonRPCEndpointBundle\Serialization\JsonRpcResponseNormalizer;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectCallController;

/**
 * DirectCall控制器完整测试类
 */
class DirectCallControllerTest extends TestCase
{
    private DirectCallController $controller;
    
    /** @var JsonRpcEndpoint|MockObject */
    private $mockEndpoint;
    
    /** @var LoggerInterface|MockObject */
    private $mockLogger;
    
    /** @var Encryptor|MockObject */
    private $mockEncryptor;
    
    /** @var JsonRpcResponseNormalizer|MockObject */
    private $mockResponseNormalizer;

    protected function setUp(): void
    {
        $this->mockEndpoint = $this->createMock(JsonRpcEndpoint::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockEncryptor = $this->createMock(Encryptor::class);
        $this->mockResponseNormalizer = $this->createMock(JsonRpcResponseNormalizer::class);

        $this->controller = new DirectCallController(
            $this->mockEndpoint,
            $this->mockLogger,
            $this->mockEncryptor,
            $this->mockResponseNormalizer
        );
    }

    /**
     * 测试__invoke方法 - 正常JSON请求处理
     */
    public function testInvoke_withValidJsonRequest_returnsSuccessResponse(): void
    {
        $requestData = ['param1' => 'value1', 'param2' => 'value2'];
        $requestContent = json_encode($requestData);
        $prefix = 'test';
        $method = 'testMethod';
        $responseContent = '{"jsonrpc":"2.0","result":"success","id":"test-123"}';

        $request = new Request([], [], [], [], [], [], $requestContent);
        $request->headers->set('request-id', 'custom-id');

        $this->mockEncryptor->expects($this->exactly(2))
            ->method('shouldEncrypt')
            ->with($request)
            ->willReturn(false);

        $expectedJsonRpcRequest = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $requestData,
            'id' => 'custom-id',
        ];

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with(json_encode($expectedJsonRpcRequest), $request)
            ->willReturn($responseContent);

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($responseContent, $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * 测试__invoke方法 - 加密请求处理
     */
    public function testInvoke_withEncryptedRequest_decryptsAndEncryptsResponse(): void
    {
        $encryptedContent = 'ENCRYPTED_DATA';
        $decryptedContent = '{"param":"value"}';
        $prefix = 'secure';
        $method = 'secureMethod';
        $responseContent = '{"jsonrpc":"2.0","result":"encrypted_success","id":"sec-123"}';
        $encryptedResponse = 'ENCRYPTED_RESPONSE';

        $request = new Request([], [], [], [], [], [], $encryptedContent);

        $this->mockEncryptor->expects($this->exactly(2))
            ->method('shouldEncrypt')
            ->with($request)
            ->willReturn(true);

        $this->mockEncryptor->expects($this->once())
            ->method('decryptByRequest')
            ->with($request, $encryptedContent)
            ->willReturn($decryptedContent);

        $this->mockLogger->expects($this->once())
            ->method('debug')
            ->with('解密JsonRPC提交数据', [
                'raw' => $encryptedContent,
                'dec' => $decryptedContent,
            ]);

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->willReturn($responseContent);

        $this->mockEncryptor->expects($this->once())
            ->method('encryptByRequest')
            ->with($request, $responseContent)
            ->willReturn($encryptedResponse);

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertEquals($encryptedResponse, $response->getContent());
    }

    /**
     * 测试__invoke方法 - 无request-id头的处理
     */
    public function testInvoke_withoutRequestIdHeader_generatesUuidId(): void
    {
        $prefix = 'test';
        $method = 'testMethod';
        $requestContent = '{"data":"value"}';

        $request = new Request([], [], [], [], [], [], $requestContent);

        $this->mockEncryptor->expects($this->exactly(2))
            ->method('shouldEncrypt')
            ->willReturn(false);

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with($this->callback(function ($jsonContent) use ($prefix) {
                $data = json_decode($jsonContent, true);
                return str_starts_with($data['id'], $prefix);
            }), $request)
            ->willReturn('{"jsonrpc":"2.0","result":"success","id":"generated-id"}');

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * 测试__invoke方法 - 空请求内容处理
     */
    public function testInvoke_withEmptyContent_handlesEmptyParams(): void
    {
        $prefix = 'test';
        $method = 'emptyMethod';
        $request = new Request();

        $this->mockEncryptor->expects($this->exactly(2))
            ->method('shouldEncrypt')
            ->willReturn(false);

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with($this->callback(function ($jsonContent) {
                $data = json_decode($jsonContent, true);
                return $data['params'] === [];
            }), $request)
            ->willReturn('{"jsonrpc":"2.0","result":"empty_handled","id":"empty-123"}');

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * 测试__invoke方法 - 无效JSON处理（JSON解析失败）
     */
    public function testInvoke_withInvalidJson_handlesAsEmptyParams(): void
    {
        $prefix = 'test';
        $method = 'invalidMethod';
        $invalidJson = '{invalid json}';
        $request = new Request([], [], [], [], [], [], $invalidJson);

        $this->mockEncryptor->expects($this->exactly(2))
            ->method('shouldEncrypt')
            ->willReturn(false);

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with('JSON数据反序列化失败', $this->callback(function ($context) use ($invalidJson) {
                return $context['content'] === $invalidJson && isset($context['error']);
            }));

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with($this->callback(function ($jsonContent) {
                $data = json_decode($jsonContent, true);
                return $data['params'] === [];
            }), $request)
            ->willReturn('{"jsonrpc":"2.0","result":"invalid_handled","id":"invalid-123"}');

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * 测试__invoke方法 - request-id包含HTML字符的处理
     */
    public function testInvoke_withHtmlInRequestId_sanitizesRequestId(): void
    {
        $prefix = 'test';
        $method = 'sanitizeMethod';
        $requestContent = '{"data":"test"}';
        $maliciousId = '<script>alert("xss")</script>';
        $sanitizedId = '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';

        $request = new Request([], [], [], [], [], [], $requestContent);
        $request->headers->set('request-id', $maliciousId);

        $this->mockEncryptor->expects($this->exactly(2))
            ->method('shouldEncrypt')
            ->willReturn(false);

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with($this->callback(function ($jsonContent) use ($sanitizedId) {
                $data = json_decode($jsonContent, true);
                return $data['id'] === $sanitizedId;
            }), $request)
            ->willReturn('{"jsonrpc":"2.0","result":"sanitized","id":"' . $sanitizedId . '"}');

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * 测试__invoke方法 - 端点异常处理
     */
    public function testInvoke_withEndpointException_returnsErrorResponse(): void
    {
        $prefix = 'test';
        $method = 'errorMethod';
        $requestContent = '{"data":"test"}';
        $request = new Request([], [], [], [], [], [], $requestContent);
        $request->headers->set('request-id', 'error-test-id');

        $this->mockEncryptor->expects($this->once())
            ->method('shouldEncrypt')
            ->willReturn(false);

        $exception = new \RuntimeException('Test endpoint error');
        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->willThrowException($exception);

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with('发生未知的JSON-RPC异常', [
                'exception' => $exception,
            ]);

        $mockJsonRpcResponse = new JsonRpcResponse();
        $mockJsonRpcResponse->setId('error-test-id');
        $mockJsonRpcResponse->setError(new JsonRpcException(-1, 'Test endpoint error', previous: $exception));

        $normalizedResponse = [
            'jsonrpc' => '2.0',
            'error' => ['code' => -1, 'message' => 'Test endpoint error'],
            'id' => 'error-test-id'
        ];

        $this->mockResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->willReturn($normalizedResponse);

        $response = $this->controller->__invoke($prefix, $method, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
        $this->assertArrayHasKey('error', $responseData);
    }
}