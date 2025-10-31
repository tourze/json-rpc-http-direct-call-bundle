# JSON-RPC HTTP Direct Call Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![License](https://img.shields.io/packagist/l/tourze/json-rpc-http-direct-call-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/json-rpc-http-direct-call-bundle)
[![codecov](https://codecov.io/gh/tourze/php-monorepo/branch/master/graph/badge.svg?token=CODECOV_TOKEN)](https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle that provides HTTP direct call functionality for JSON-RPC services. This bundle offers an alternative approach to JSON-RPC invocation through HTTP endpoints.

## Features

- HTTP direct calls to JSON-RPC services
- Request/response encryption and decryption support
- Multiple calling interface forms
- Automatic route loading with attributes
- Built-in error handling and logging
- Support for prefixed and non-prefixed endpoints

## Requirements

- PHP 8.2 or higher
- Symfony 7.3 or higher
- ext-json

## Installation

```bash
composer require tourze/json-rpc-http-direct-call-bundle
```

## Configuration

Add the bundle to your `config/bundles.php` file:

```php
return [
    // ...
    Tourze\JsonRPCHttpDirectCallBundle\JsonRPCHttpDirectCallBundle::class => ['all' => true],
];
```

## Quick Start

### Direct Call Endpoint

Send POST requests to JSON-RPC methods using these endpoints:

```bash
# With prefix
POST /json-rpc/{prefix}/{method}.aspx

# Without prefix (CP endpoint)
POST /cp/json-rpc/{method}.aspx
```

Example request:
```bash
curl -X POST http://localhost/json-rpc/user/getInfo.aspx \
  -H "Content-Type: application/json" \
  -H "request-id: unique-request-id" \
  -d '{"userId": 123}'
```

### Direct POST Endpoint

Send POST requests directly to methods:

```bash
POST /json-rpc/call/{method}
```

Example request:
```bash
curl -X POST http://localhost/json-rpc/call/getUser \
  -d "userId=123&includeProfile=true"
```

## Usage

### Controllers

The bundle provides two main controllers:

1. **DirectCallController** - Handles requests to `/json-rpc/{prefix}/{method}.aspx` and `/cp/json-rpc/{method}.aspx`
2. **DirectPostController** - Handles requests to `/json-rpc/call/{method}`

### Encryption Support

The bundle supports request/response encryption when the encryptor service detects encrypted endpoints:

```php
// Automatic encryption detection based on request path
if ($this->encryptor->shouldEncrypt($request)) {
    $decryptedContent = $this->encryptor->decryptByRequest($request, $content);
    // Process decrypted content...
}
```

### Request ID Handling

The bundle automatically handles request IDs:
- Uses `request-id` header if provided
- Generates UUID v4 if not provided
- Prefixes with endpoint prefix for tracking

## Architecture

```text
src/
├── Controller/
│   ├── DirectCallController.php    # Main JSON-RPC endpoint controller
│   └── DirectPostController.php    # Direct POST endpoint controller
├── DependencyInjection/
│   └── JsonRPCHttpDirectCallExtension.php  # Service configuration
├── Exception/
│   └── UnexpectedControllerException.php   # Custom exceptions
├── Service/
│   └── AttributeControllerLoader.php       # Route auto-loader
└── JsonRPCHttpDirectCallBundle.php         # Bundle class
```

## Testing

Run tests from the project root directory:

```bash
./vendor/bin/phpunit packages/json-rpc-http-direct-call-bundle/tests
```

Test coverage includes:
- Unit tests for all components
- Integration tests for complete workflows
- Mock services for dependencies

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
