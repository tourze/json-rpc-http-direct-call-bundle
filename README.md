# JSON-RPC HTTP Direct Call Bundle

JsonRPC另外一种调用方式，为Symfony应用提供JSON-RPC HTTP直接调用功能。

## 特性

- 支持HTTP直接调用JSON-RPC服务
- 支持加密和解密请求/响应
- 提供多种调用接口形式

## 安装

```bash
composer require tourze/json-rpc-http-direct-call-bundle
```

## 配置

将Bundle添加到你的`config/bundles.php`文件中：

```php
return [
    // ...
    Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle::class => ['all' => true],
];
```

## 使用方法

### 通过HTTP POST调用JSON-RPC方法

```php
// 发送POST请求到 /json-rpc/{前缀}/{方法名}.aspx
// 或者 /cp/json-rpc/{方法名}.aspx
```

### 通过直接POST调用

```php
// 发送POST请求到 /json-rpc/call/{方法名}
```

## 单元测试

在项目根目录执行以下命令运行单元测试：

```bash
./vendor/bin/phpunit packages/json-rpc-http-direct-call-bundle/tests
```

## 测试架构

测试目录结构与源代码目录结构保持一致：

```
tests/
├── Controller/          # 控制器测试
├── DependencyInjection/ # 依赖注入扩展测试
├── Integration/         # 集成测试
├── Service/             # 服务测试
└── .gitignore           # Git忽略配置
```

## 开发说明

1. 主要类和功能：
   - `JsonRPCHttpDirectCallBundle` - 主Bundle类
   - `JsonRpcController` - 提供直接调用JSON-RPC服务的控制器
   - `AttributeControllerLoader` - 自动加载控制器路由的服务

2. 控制器提供两种调用方式：
   - `directCall` - 通过 `/json-rpc/{prefix}/{method}.aspx` 或 `/cp/json-rpc/{method}.aspx` 路径调用
   - `directPost` - 通过 `/json-rpc/call/{method}` 路径调用

3. 支持加密请求和响应，通过 `encryptor` 服务处理加密和解密
