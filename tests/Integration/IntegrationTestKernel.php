<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle;

class IntegrationTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new JsonRPCHttpDirectCallBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test',
        ]);

        // 加载测试服务配置
        $loader->load(__DIR__ . '/services.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // 不需要额外的路由配置，会自动加载Controller中的路由
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/logs';
    }
}
