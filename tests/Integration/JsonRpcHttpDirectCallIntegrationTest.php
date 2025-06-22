<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectCallController;
use Tourze\JsonRPCHttpDirectCallBundle\Controller\DirectPostController;
use Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle;
use Tourze\JsonRPCHttpDirectCallBundle\Service\AttributeControllerLoader;

/**
 * 集成测试类 - 专注于核心功能测试
 */
class JsonRpcHttpDirectCallIntegrationTest extends TestCase
{
    /**
     * 测试AttributeControllerLoader的基本功能
     */
    public function testAttributeControllerLoader_basicFunctionality(): void
    {
        $loader = new AttributeControllerLoader();
        
        // 测试supports方法
        $this->assertFalse($loader->supports('any_resource'));
        $this->assertFalse($loader->supports('any_resource', 'any_type'));
        
        // 测试autoload方法返回路由集合
        $routes = $loader->autoload();
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $routes);
        
        // 验证路由是否被加载
        $allRoutes = $routes->all();
        $this->assertNotEmpty($allRoutes);
        
        $routeNames = array_keys($allRoutes);
        $this->assertContains('rpc_http_server_caller', $routeNames);
        $this->assertContains('json_rpc_cp_caller', $routeNames);
        $this->assertContains('json_rpc_http_post_caller', $routeNames);
    }

    /**
     * 测试路由配置的正确性
     */
    public function testRouteConfiguration_routesAreConfiguredCorrectly(): void
    {
        $loader = new AttributeControllerLoader();
        $routes = $loader->autoload();
        
        // 测试 rpc_http_server_caller 路由
        $rpcRoute = $routes->get('rpc_http_server_caller');
        $this->assertNotNull($rpcRoute);
        $this->assertEquals('/json-rpc/{prefix}/{method}.aspx', $rpcRoute->getPath());
        $this->assertEquals(['POST'], $rpcRoute->getMethods());
        
        // 测试 json_rpc_cp_caller 路由
        $cpRoute = $routes->get('json_rpc_cp_caller');
        $this->assertNotNull($cpRoute);
        $this->assertEquals('/cp/json-rpc/{method}.aspx', $cpRoute->getPath());
        $this->assertEquals(['POST'], $cpRoute->getMethods());
        $this->assertEquals('', $cpRoute->getDefault('prefix'));
        
        // 测试 json_rpc_http_post_caller 路由
        $postRoute = $routes->get('json_rpc_http_post_caller');
        $this->assertNotNull($postRoute);
        $this->assertEquals('/json-rpc/call/{method}', $postRoute->getPath());
    }

    /**
     * 测试load方法调用autoload
     */
    public function testLoad_callsAutoload(): void
    {
        $loader = new AttributeControllerLoader();
        
        $result1 = $loader->autoload();
        $result2 = $loader->load('any_resource', 'any_type');
        
        // 两个方法应该返回相同的内容
        $this->assertEquals($result1->all(), $result2->all());
        $this->assertEquals(count($result1->all()), count($result2->all()));
    }

    /**
     * 测试MockEncryptor的基本功能
     */
    public function testMockEncryptor_basicFunctionality(): void
    {
        // 为了解决父类构造函数的问题，我们传入一个null参数
        $mockEncryptor = new class extends Mock\MockEncryptor {
            public function __construct() {
                // 跳过父类构造函数
            }
        };
        
        // 测试普通路径不需要加密
        $normalRequest = \Symfony\Component\HttpFoundation\Request::create('/json-rpc/test/method.aspx');
        $this->assertFalse($mockEncryptor->shouldEncrypt($normalRequest));
        
        // 测试包含encrypt的路径需要加密
        $encryptRequest = \Symfony\Component\HttpFoundation\Request::create('/json-rpc/encrypt/method.aspx');
        $this->assertTrue($mockEncryptor->shouldEncrypt($encryptRequest));
        
        // 测试解密功能
        $encryptedContent = 'ENCRYPTED:test_data';
        $decrypted = $mockEncryptor->decryptByRequest($normalRequest, $encryptedContent);
        $this->assertEquals('test_data', $decrypted);
        
        // 测试加密功能
        $plainContent = 'plain_data';
        $encrypted = $mockEncryptor->encryptByRequest($normalRequest, $plainContent);
        $this->assertEquals('ENCRYPTED:plain_data', $encrypted);
    }

    /**
     * 测试MockJsonRpcEndpoint的基本功能
     */
    public function testMockJsonRpcEndpoint_basicFunctionality(): void
    {
        // 创建一个继承类来解决构造函数问题
        $mockEndpoint = new class() extends Mock\MockJsonRpcEndpoint {
            public function __construct() {
                // 跳过父类构造函数
            }
        };
        
        // 测试基本的index方法功能
        $testJson = '{"jsonrpc":"2.0","method":"test","id":"123","params":{"data":"test"}}';
        $result = $mockEndpoint->index($testJson);
        
        $resultData = json_decode($result, true);
        $this->assertEquals('2.0', $resultData['jsonrpc']);
        $this->assertEquals('mocked_result', $resultData['result']);
        $this->assertEquals('123', $resultData['id']);
    }

    /**
     * 测试Bundle类的基本功能
     */
    public function testBundle_basicFunctionality(): void
    {
        $bundle = new \Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle();
        
        // 验证Bundle类存在并可实例化
        $this->assertInstanceOf('Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle', $bundle);
        
        // 验证Bundle类继承自正确的基类
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', $bundle);
        
        // 测试boot方法可以正常调用
        $bundle->boot();
        $this->assertTrue(true, 'Bundle boot method executed without exceptions');
    }

    /**
     * 测试DependencyInjection扩展类的基本功能
     */
    public function testDependencyInjectionExtension_basicFunctionality(): void
    {
        $extension = new \Tourze\JsonRPCHttpDirectCallBundle\DependencyInjection\JsonRPCHttpDirectCallExtension();
        
        // 验证扩展类存在并可实例化
        $this->assertInstanceOf('Tourze\JsonRPCHttpDirectCallBundle\DependencyInjection\JsonRPCHttpDirectCallExtension', $extension);
        
        // 验证扩展类继承自正确的基类
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Extension\Extension', $extension);
    }

    /**
     * 测试控制器类的基本结构
     */
    public function testController_basicStructure(): void
    {
        // 验证控制器类存在
        $this->assertTrue(class_exists(DirectCallController::class));
        $this->assertTrue(class_exists(DirectPostController::class));
        
        $directCallReflection = new \ReflectionClass(DirectCallController::class);
        $directPostReflection = new \ReflectionClass(DirectPostController::class);
        
        // 验证控制器有正确的方法
        $this->assertTrue($directCallReflection->hasMethod('__invoke'));
        $this->assertTrue($directPostReflection->hasMethod('__invoke'));
        
        // 验证方法是public的
        $directCallInvokeMethod = $directCallReflection->getMethod('__invoke');
        $this->assertTrue($directCallInvokeMethod->isPublic());
        
        $directPostInvokeMethod = $directPostReflection->getMethod('__invoke');
        $this->assertTrue($directPostInvokeMethod->isPublic());
        
        // 验证构造函数参数数量
        $directCallConstructor = $directCallReflection->getConstructor();
        $this->assertNotNull($directCallConstructor);
        $this->assertCount(4, $directCallConstructor->getParameters());
        
        $directPostConstructor = $directPostReflection->getConstructor();
        $this->assertNotNull($directPostConstructor);
        $this->assertCount(2, $directPostConstructor->getParameters());
    }

    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected static function createKernel(array $options = []): IntegrationTestKernel
    {
        $appendBundles = [
            FrameworkBundle::class => ['all' => true],
            JsonRPCHttpDirectCallBundle::class => ['all' => true],
        ];
        
        $entityMappings = [];

        return new IntegrationTestKernel(
            $options['environment'] ?? 'test',
            $options['debug'] ?? true,
            $appendBundles,
            $entityMappings
        );
    }
}
