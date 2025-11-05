<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectCallController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * DirectCall控制器HTTP端点测试类
 * 使用真实HTTP请求测试Controller的端点行为
 *
 * @internal
 */
#[CoversClass(DirectCallController::class)]
#[RunTestsInSeparateProcesses]
final class DirectCallControllerTest extends AbstractWebTestCase
{
    public function testPostRequestWithValidJsonReturnsJsonResponse(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['param1' => 'value1', 'param2' => 'value2'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $client->request('POST', '/json-rpc/test/testMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_REQUEST_ID' => 'test-request-id',
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertEquals('2.0', $responseData['jsonrpc']);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testPostRequestWithEmptyContentReturnsJsonResponse(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('POST', '/json-rpc/test/emptyMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    public function testPostRequestWithInvalidJsonReturnsJsonResponse(): void
    {
        $client = self::createClientWithDatabase();

        $invalidJson = '{invalid json}';

        $client->request('POST', '/json-rpc/test/invalidMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $invalidJson);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    public function testPostRequestWithoutRequestIdGeneratesId(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['data' => 'value'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $client->request('POST', '/json-rpc/test/testMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        // The ID should be generated when no request-id header is provided
        $this->assertIsString($responseData['id']);
        $this->assertNotEmpty($responseData['id']);
    }

    public function testPostRequestWithMaliciousRequestIdSanitizesId(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['data' => 'test'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);
        $maliciousId = '<script>alert("xss")</script>';

        $client->request('POST', '/json-rpc/test/sanitizeMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_REQUEST_ID' => $maliciousId,
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertIsString($responseData['id']);
        $this->assertStringContainsString('&lt;script&gt;', $responseData['id']);
    }

    public function testCpRouteWithoutPrefixWorks(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['param' => 'value'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $client->request('POST', '/cp/json-rpc/cpMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_REQUEST_ID' => 'cp-test-id',
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/json-rpc/test/testMethod.aspx');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/json-rpc/test/testMethod.aspx');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/json-rpc/test/testMethod.aspx');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/json-rpc/test/testMethod.aspx');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/json-rpc/test/testMethod.aspx');
    }

    public function testUnauthenticatedAccessAllowed(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['param' => 'value'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $client->request('POST', '/json-rpc/public/publicMethod.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    public function testResponseHeadersAreCorrect(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['test' => 'data'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $client->request('POST', '/json-rpc/test/headerTest.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->getContent());
    }

    public function testDifferentPrefixesWork(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['data' => 'test'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $prefixes = ['api', 'service', 'endpoint', 'rpc'];

        foreach ($prefixes as $prefix) {
            $client->request('POST', "/json-rpc/{$prefix}/testMethod.aspx", [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], $requestContent);

            $response = $client->getResponse();
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('application/json', $response->headers->get('Content-Type'));
            $content = $response->getContent();
            $this->assertIsString($content);
            $this->assertJson($content);
        }
    }

    public function testDifferentMethodNamesWork(): void
    {
        $client = self::createClientWithDatabase();

        $requestData = ['param' => 'value'];
        $requestContent = json_encode($requestData);
        $this->assertIsString($requestContent);

        $methods = ['getUserInfo', 'createUser', 'updateProfile', 'deleteRecord'];

        foreach ($methods as $method) {
            $client->request('POST', "/json-rpc/test/{$method}.aspx", [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], $requestContent);

            $response = $client->getResponse();
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('application/json', $response->headers->get('Content-Type'));
            $content = $response->getContent();
            $this->assertIsString($content);
            $this->assertJson($content);
        }
    }

    public function testLargeJsonPayloadHandling(): void
    {
        $client = self::createClientWithDatabase();

        $largeData = [];
        for ($i = 0; $i < 1000; ++$i) {
            $largeData["key_{$i}"] = "value_{$i}_" . str_repeat('x', 100);
        }
        $requestContent = json_encode($largeData);
        $this->assertIsString($requestContent);

        $client->request('POST', '/json-rpc/test/largePayload.aspx', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $requestContent);

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/json-rpc/test/testMethod.aspx');
    }
}
