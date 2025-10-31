<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectPostController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * DirectPostController 真实 HTTP 请求测试
 *
 * @internal
 */
#[CoversClass(DirectPostController::class)]
#[RunTestsInSeparateProcesses]
final class DirectPostControllerTest extends AbstractWebTestCase
{
    public function testPostRequestWithFormDataConvertsToJsonRpcRequest(): void
    {
        $client = self::createClientWithDatabase();

        $formData = [
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com',
        ];

        $client->request('POST', '/json-rpc/call/testMethod', $formData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testPostRequestWithEmptyFormDataHandlesEmptyParams(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('POST', '/json-rpc/call/emptyMethod', [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testPostRequestWithSpecialCharactersHandlesCorrectly(): void
    {
        $client = self::createClientWithDatabase();

        $formData = [
            'html' => '<script>alert("test")</script>',
            'unicode' => '测试中文',
            'special' => '!@#$%^&*()',
            'quotes' => 'He said "Hello"',
        ];

        $client->request('POST', '/json-rpc/call/specialMethod', $formData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testPostRequestWithNestedArrayData(): void
    {
        $client = self::createClientWithDatabase();

        $formData = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30,
                    'city' => 'Beijing',
                ],
            ],
            'preferences' => ['color' => 'blue', 'theme' => 'dark'],
        ];

        $client->request('POST', '/json-rpc/call/nestedMethod', $formData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testPostRequestWithLargeFormData(): void
    {
        $client = self::createClientWithDatabase();

        $formData = [];
        for ($i = 0; $i < 100; ++$i) {
            $formData["field_{$i}"] = "value_{$i}";
        }

        $client->request('POST', '/json-rpc/call/largeDataMethod', $formData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testGetRequestWithoutParametersReturnsError(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/json-rpc/call/testMethod', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testUnauthenticatedAccessIsAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $formData = [
            'public' => 'data',
        ];

        $client->request('POST', '/json-rpc/call/publicMethod', $formData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    public function testResponseContainsCorrectHeaders(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('POST', '/json-rpc/call/headerTest', ['test' => 'value'], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertTrue($response->headers->has('Content-Type'));
    }

    public function testDifferentMethodNamesAreHandledCorrectly(): void
    {
        $client = self::createClientWithDatabase();

        $methods = ['method1', 'method_2', 'method-3', 'CamelCaseMethod', 'snake_case_method'];

        foreach ($methods as $method) {
            $client->request('POST', "/json-rpc/call/{$method}", ['data' => 'test'], [], [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'HTTP_ACCEPT' => 'application/json',
            ]);

            $response = $client->getResponse();
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('application/json', $response->headers->get('Content-Type'));
            $content = $response->getContent();
            $this->assertIsString($content);
            $this->assertJson($content);

            $responseData = json_decode($content, true);
            $this->assertIsArray($responseData);
            $this->assertArrayHasKey('jsonrpc', $responseData);
            $this->assertEquals('2.0', $responseData['jsonrpc']);
        }
    }

    public function testEmptyMethodNameHandling(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(NotFoundHttpException::class);
        $client->request('POST', '/json-rpc/call/', ['data' => 'test'], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }

    public function testFileUploadParametersAreIgnored(): void
    {
        $client = self::createClientWithDatabase();

        $files = [];
        $parameters = ['text_field' => 'value'];

        $client->request('POST', '/json-rpc/call/uploadMethod', $parameters, $files, [
            'CONTENT_TYPE' => 'multipart/form-data',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('jsonrpc', $responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/json-rpc/call/testMethod');
    }
}
