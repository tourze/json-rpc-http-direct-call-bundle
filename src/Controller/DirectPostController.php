<?php

namespace Tourze\JsonRPCHttpDirectCallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Factory\UuidFactory;
use Tourze\JsonRPCEndpointBundle\Service\JsonRpcEndpoint as SDKJsonRpcEndpoint;

class DirectPostController extends AbstractController
{
    public function __construct(
        private readonly SDKJsonRpcEndpoint $sdkEndpoint,
        private readonly UuidFactory $uuidFactory,
    )
    {
    }

    /**
     * 直接调用接口
     */
    #[Route(path: '/json-rpc/call/{method}', name: 'json_rpc_http_post_caller')]
    public function __invoke(string $method, Request $request): Response
    {
        // 构造一个JSON-RPC request
        $json = [
            'jsonrpc' => '2.0',
            'id' => $this->uuidFactory->create()->toRfc4122(),
            'method' => $method,
            'params' => $request->request->all(),
        ];

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $response->setContent($this->sdkEndpoint->index(json_encode($json), $request));

        return $response;
    }
}