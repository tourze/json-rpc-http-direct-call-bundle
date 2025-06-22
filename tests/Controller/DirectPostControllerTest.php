<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\Uuid;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectPostController;

/**
 * DirectPost控制器完整测试类
 */
class DirectPostControllerTest extends TestCase
{
    private DirectPostController $controller;
    
    /** @var JsonRpcEndpoint|MockObject */
    private $mockEndpoint;
    
    /** @var UuidFactory|MockObject */
    private $mockUuidFactory;

    /**
     * 测试__invoke方法 - 正常表单请求处理
     */
    public function testInvoke_withFormData_convertsToJsonRpcRequest(): void
    {
        $method = 'postMethod';
        $formData = ['name' => 'John', 'age' => 30];
        $mockUuid = $this->createMock(Uuid::class);
        $mockUuid->expects($this->once())
            ->method('toRfc4122')
            ->willReturn('post-uuid-123');

        $this->mockUuidFactory->expects($this->once())
            ->method('create')
            ->willReturn($mockUuid);

        $request = new Request([], $formData);
        $responseContent = '{"jsonrpc":"2.0","result":"form_processed","id":"post-uuid-123"}';

        $expectedJsonRpcRequest = [
            'jsonrpc' => '2.0',
            'id' => 'post-uuid-123',
            'method' => $method,
            'params' => $formData,
        ];

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with(json_encode($expectedJsonRpcRequest), $request)
            ->willReturn($responseContent);

        $response = $this->controller->__invoke($method, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($responseContent, $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * 测试__invoke方法 - 空表单数据处理
     */
    public function testInvoke_withEmptyFormData_handlesEmptyParams(): void
    {
        $method = 'emptyPostMethod';
        $mockUuid = $this->createMock(Uuid::class);
        $mockUuid->expects($this->once())
            ->method('toRfc4122')
            ->willReturn('empty-post-uuid');

        $this->mockUuidFactory->expects($this->once())
            ->method('create')
            ->willReturn($mockUuid);

        $request = new Request();
        $responseContent = '{"jsonrpc":"2.0","result":"empty_form_handled","id":"empty-post-uuid"}';

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with($this->callback(function ($jsonContent) {
                $data = json_decode($jsonContent, true);
                return $data['params'] === [];
            }), $request)
            ->willReturn($responseContent);

        $response = $this->controller->__invoke($method, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($responseContent, $response->getContent());
    }

    /**
     * 测试__invoke方法 - 特殊字符表单数据处理
     */
    public function testInvoke_withSpecialCharacters_handlesCorrectly(): void
    {
        $method = 'specialMethod';
        $formData = [
            'html' => '<script>alert("test")</script>',
            'unicode' => '测试中文',
            'special' => '!@#$%^&*()',
        ];

        $mockUuid = $this->createMock(Uuid::class);
        $mockUuid->expects($this->once())
            ->method('toRfc4122')
            ->willReturn('special-uuid');

        $this->mockUuidFactory->expects($this->once())
            ->method('create')
            ->willReturn($mockUuid);

        $request = new Request([], $formData);
        $responseContent = '{"jsonrpc":"2.0","result":"special_handled","id":"special-uuid"}';

        $this->mockEndpoint->expects($this->once())
            ->method('index')
            ->with($this->callback(function ($jsonContent) use ($formData) {
                $data = json_decode($jsonContent, true);
                return $data['params'] === $formData;
            }), $request)
            ->willReturn($responseContent);

        $response = $this->controller->__invoke($method, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($responseContent, $response->getContent());
    }

    protected function setUp(): void
    {
        $this->mockEndpoint = $this->createMock(JsonRpcEndpoint::class);
        $this->mockUuidFactory = $this->createMock(UuidFactory::class);

        $this->controller = new DirectPostController(
            $this->mockEndpoint,
            $this->mockUuidFactory
        );
    }
}