# JSON-RPC HTTP 直接调用包

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![License](https://img.shields.io/packagist/l/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![codecov](https://codecov.io/gh/tourze/php-monorepo/branch/master/graph/badge.svg?token=CODECOV_TOKEN)](https://codecov.io/gh/tourze/php-monorepo)

一个为 JSON-RPC 服务提供 HTTP 直接调用功能的 Symfony 包。该包提供了通过 HTTP 端点调用 JSON-RPC 的替代方案。

## 特性

- 支持 HTTP 直接调用 JSON-RPC 服务
- 支持请求/响应加密和解密
- 提供多种调用接口形式
- 基于注解的自动路由加载
- 内置错误处理和日志记录
- 支持带前缀和不带前缀的端点

## 系统要求

- PHP 8.2 或更高版本
- Symfony 7.3 或更高版本
- ext-json 扩展

## 安装

```bash
composer require tourze/json-rpc-http-direct-call-bundle
```

## 配置

将包添加到你的 `config/bundles.php` 文件中：

```php
return [
    // ...
    Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle::class => ['all' => true],
];
```

## 快速开始

### 直接调用端点

使用以下端点向 JSON-RPC 方法发送 POST 请求：

```bash
# 带前缀
POST /json-rpc/{prefix}/{method}.aspx

# 不带前缀（CP 端点）
POST /cp/json-rpc/{method}.aspx
```

请求示例：
```bash
curl -X POST http://localhost/json-rpc/user/getInfo.aspx \
  -H "Content-Type: application/json" \
  -H "request-id: unique-request-id" \
  -d '{"userId": 123}'
```

### 直接 POST 端点

直接向方法发送 POST 请求：

```bash
POST /json-rpc/call/{method}
```

请求示例：
```bash
curl -X POST http://localhost/json-rpc/call/getUser \
  -d "userId=123&includeProfile=true"
```

## 使用方法

### 控制器

该包提供两个主要控制器：

1. **DirectCallController** - 处理 `/json-rpc/{prefix}/{method}.aspx` 和 `/cp/json-rpc/{method}.aspx` 的请求
2. **DirectPostController** - 处理 `/json-rpc/call/{method}` 的请求

### 加密支持

当加密器服务检测到加密端点时，该包支持请求/响应加密：

```php
// 基于请求路径的自动加密检测
if ($this->encryptor->shouldEncrypt($request)) {
    $decryptedContent = $this->encryptor->decryptByRequest($request, $content);
    // 处理解密后的内容...
}
```

### 请求 ID 处理

该包自动处理请求 ID：
- 如果提供了 `request-id` 头，则使用该头
- 如果未提供，则生成 UUID v4
- 使用端点前缀进行跟踪

## 架构

```text
src/
├── Controller/
│   ├── DirectCallController.php    # 主要的 JSON-RPC 端点控制器
│   └── DirectPostController.php    # 直接 POST 端点控制器
├── DependencyInjection/
│   └── JsonRPCHttpDirectCallExtension.php  # 服务配置
├── Exception/
│   └── UnexpectedControllerException.php   # 自定义异常
├── Service/
│   └── AttributeControllerLoader.php       # 路由自动加载器
└── JsonRPCHttpDirectCallBundle.php         # 包类
```

## 测试

从项目根目录运行测试：

```bash
./vendor/bin/phpunit packages/json-rpc-http-direct-call-bundle/tests
```

测试覆盖范围包括：
- 所有组件的单元测试
- 完整工作流的集成测试
- 依赖项的模拟服务

## 贡献

1. Fork 仓库
2. 创建你的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交你的更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 打开一个 Pull Request

## 许可证

MIT 许可证。详情请见 [许可证文件](LICENSE)。
