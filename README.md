# JingdongLdopBundle

![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)
![Symfony Version](https://img.shields.io/badge/symfony-%5E6.4-green.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)
![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle for integrating with JingDong Logistics API (京东物流 API).

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Requirements](#requirements)
- [API Methods](#api-methods)
- [Entities](#entities)
- [Advanced Usage](#advanced-usage)
- [Security](#security)
- [Exceptions](#exceptions)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)

## Features

- **Pickup Order Management**: Create and cancel pickup orders
- **Logistics Tracking**: Real-time tracking of package status
- **Entity Management**: Doctrine entities for order and logistics data
- **Error Handling**: Comprehensive exception handling
- **Configuration Management**: Flexible API configuration
- **Audit Logging**: Detailed logging for all API interactions

## Installation

Install the bundle using Composer:

```bash
composer require tourze/jingdong-ldop-bundle
```

## Quick Start

### 1. Configure the Bundle

Add the bundle to your `config/bundles.php`:

```php
<?php
return [
    // ...
    JingdongLdopBundle\JingdongLdopBundle::class => ['all' => true],
];
```

### 2. Database Configuration

Create and run migrations for the required entities:

```bash
php bin/console doctrine:migrations:migrate
```

### 3. API Configuration

Configure your JingDong Logistics API credentials by creating a `JdlConfig` entity:

```php
use JingdongLdopBundle\Entity\JdlConfig;

$config = new JdlConfig();
$config->setCustomerCode('your-customer-code');
$config->setAppKey('your-app-key'); 
$config->setSecret('your-secret');
$config->setApiHost('https://api.jd.com/routerjson');
$config->setIsDefault(true);

$entityManager->persist($config);
$entityManager->flush();
```

### 4. Create Pickup Orders

```php
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Service\JdlService;

// Create a pickup order
$pickupOrder = new PickupOrder();
$pickupOrder->setSenderName('张三')
    ->setSenderMobile('13800138000')
    ->setSenderAddress('北京市朝阳区xxx路xxx号')
    ->setReceiverName('李四')
    ->setReceiverMobile('13900139000')
    ->setReceiverAddress('上海市浦东新区xxx路xxx号')
    ->setWeight(1.5)
    ->setPackageQuantity(1);

// Submit the order
$result = $jdlService->createPickupOrder($pickupOrder);
```

### 5. Track Logistics

```php
// Get logistics tracking information
$waybillCode = 'JD0001234567890';
$logisticsDetails = $jdlService->getLogisticsTrace($waybillCode, $pickupOrder);
```

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+

## API Methods

### JdlService

- `createPickupOrder(PickupOrder $pickupOrder)` - Create a pickup order
- `cancelPickupOrder(PickupOrder $pickupOrder, string $reason)` - Cancel a pickup order
- `getLogisticsTrace(string $waybillCode, PickupOrder $pickupOrder)` - Get logistics tracking info

## Entities

- `JdlConfig` - API configuration storage
- `PickupOrder` - Pickup order information
- `LogisticsDetail` - Logistics tracking details
- `JdlAccessToken` - API access token management

## Advanced Usage

### Custom Configuration

You can create multiple API configurations for different environments:

```php
// Production config
$prodConfig = new JdlConfig();
$prodConfig->setCustomerCode('prod-customer-code');
$prodConfig->setAppKey('prod-app-key');
$prodConfig->setSecret('prod-secret');
$prodConfig->setIsDefault(true);

// Test config
$testConfig = new JdlConfig();
$testConfig->setCustomerCode('test-customer-code');
$testConfig->setAppKey('test-app-key');
$testConfig->setSecret('test-secret');
$testConfig->setIsDefault(false);
```

### Error Handling

The bundle provides comprehensive error handling with specific exceptions:

```php
try {
    $result = $jdlService->createPickupOrder($pickupOrder);
} catch (JdlApiException $e) {
    // Handle API-specific errors
    $logger->error('JD API Error: ' . $e->getMessage());
} catch (JdlAuthException $e) {
    // Handle authentication errors
    $logger->error('JD Auth Error: ' . $e->getMessage());
} catch (JdlConfigException $e) {
    // Handle configuration errors
    $logger->error('JD Config Error: ' . $e->getMessage());
}
```

### Logging and Monitoring

The bundle automatically logs all API interactions with detailed information:

- Request parameters (with sensitive data masked)
- Response codes and content
- Execution time
- Error details

## Security

### API Credentials

**Important**: Never commit API credentials to version control. Use environment
variables or secure configuration management:

```yaml
# config/services.yaml
parameters:
    jd.app_key: '%env(JD_APP_KEY)%'
    jd.app_secret: '%env(JD_APP_SECRET)%'
    jd.customer_code: '%env(JD_CUSTOMER_CODE)%'
```

### Data Protection

- All sensitive data in logs is automatically masked
- API signatures are hidden in debug output
- Personal information is handled according to privacy standards

### Rate Limiting

Be aware of JingDong API rate limits:
- Implement proper retry mechanisms
- Use appropriate timeouts
- Monitor API usage

## Exceptions

- `JdlApiException` - API request failures
- `JdlAuthException` - Authentication errors
- `JdlConfigException` - Configuration issues

## Contributing

We welcome contributions! Please see our contributing guidelines:

### Reporting Issues

- Use the GitHub issue tracker
- Provide clear reproduction steps
- Include environment details (PHP/Symfony versions)
- Attach relevant logs or stack traces

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes following our coding standards
4. Add tests for new functionality
5. Ensure all tests pass (`composer test`)
6. Run static analysis (`composer phpstan`)
7. Submit a pull request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/tourze/jingdong-ldop-bundle.git
cd jingdong-ldop-bundle

# Install dependencies
composer install

# Run tests
composer test

# Run static analysis
composer phpstan
```

### Coding Standards

- Follow PSR-12 coding standards
- Use PHP 8.1+ features appropriately
- Write comprehensive tests for new features
- Add appropriate type hints and documentation

## Changelog

### 1.0.0 (Current)

**Features:**
- Initial release
- Pickup order management (create/cancel)
- Real-time logistics tracking
- Comprehensive error handling
- Audit logging for all API interactions
- Doctrine entity management

**Dependencies:**
- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+

**Security:**
- Automatic sensitive data masking in logs
- Secure API credential handling
- Rate limiting awareness

## License

This bundle is released under the MIT license. See the [LICENSE](LICENSE) file for details.