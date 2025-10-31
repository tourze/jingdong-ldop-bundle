# JingdongLdopBundle

![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)
![Symfony Version](https://img.shields.io/badge/symfony-%5E6.4-green.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)
![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)

[English](README.md) | [中文](README.zh-CN.md)

一个用于集成京东物流 API 的 Symfony Bundle。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [系统要求](#系统要求)
- [API 方法](#api-方法)
- [实体](#实体)
- [高级用法](#高级用法)
- [安全性](#安全性)
- [异常](#异常)
- [贡献](#贡献)
- [更新日志](#更新日志)
- [许可证](#许可证)

## 功能特性

- **取件订单管理**：创建和取消取件订单
- **物流跟踪**：实时跟踪包裹状态
- **实体管理**：用于订单和物流数据的 Doctrine 实体
- **错误处理**：全面的异常处理
- **配置管理**：灵活的 API 配置
- **审计日志**：详细记录所有 API 交互

## 安装

使用 Composer 安装 Bundle：

```bash
composer require tourze/jingdong-ldop-bundle
```

## 快速开始

### 1. 配置 Bundle

在 `config/bundles.php` 中添加 Bundle：

```php
<?php
return [
    // ...
    JingdongLdopBundle\JingdongLdopBundle::class => ['all' => true],
];
```

### 2. 数据库配置

为所需的实体创建并运行迁移：

```bash
php bin/console doctrine:migrations:migrate
```

### 3. API 配置

通过创建 `JdlConfig` 实体来配置京东物流 API 凭证：

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

### 4. 创建取件订单

```php
use JingdongLdopBundle\Entity\PickupOrder;
use JingdongLdopBundle\Service\JdlService;

// 创建取件订单
$pickupOrder = new PickupOrder();
$pickupOrder->setSenderName('张三')
    ->setSenderMobile('13800138000')
    ->setSenderAddress('北京市朝阳区xxx路xxx号')
    ->setReceiverName('李四')
    ->setReceiverMobile('13900139000')
    ->setReceiverAddress('上海市浦东新区xxx路xxx号')
    ->setWeight(1.5)
    ->setPackageQuantity(1);

// 提交订单
$result = $jdlService->createPickupOrder($pickupOrder);
```

### 5. 物流跟踪

```php
// 获取物流跟踪信息
$waybillCode = 'JD0001234567890';
$logisticsDetails = $jdlService->getLogisticsTrace($waybillCode, $pickupOrder);
```

## 系统要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+

## API 方法

### JdlService

- `createPickupOrder(PickupOrder $pickupOrder)` - 创建取件订单
- `cancelPickupOrder(PickupOrder $pickupOrder, string $reason)` - 取消取件订单
- `getLogisticsTrace(string $waybillCode, PickupOrder $pickupOrder)` - 获取物流跟踪信息

## 实体

- `JdlConfig` - API 配置存储
- `PickupOrder` - 取件订单信息
- `LogisticsDetail` - 物流跟踪详情
- `JdlAccessToken` - API 访问令牌管理

## 高级用法

### 自定义配置

您可以为不同环境创建多个 API 配置：

```php
// 生产环境配置
$prodConfig = new JdlConfig();
$prodConfig->setCustomerCode('prod-customer-code');
$prodConfig->setAppKey('prod-app-key');
$prodConfig->setSecret('prod-secret');
$prodConfig->setIsDefault(true);

// 测试环境配置
$testConfig = new JdlConfig();
$testConfig->setCustomerCode('test-customer-code');
$testConfig->setAppKey('test-app-key');
$testConfig->setSecret('test-secret');
$testConfig->setIsDefault(false);
```

### 错误处理

Bundle 提供了具有特定异常的全面错误处理：

```php
try {
    $result = $jdlService->createPickupOrder($pickupOrder);
} catch (JdlApiException $e) {
    // 处理 API 特定错误
    $logger->error('京东 API 错误: ' . $e->getMessage());
} catch (JdlAuthException $e) {
    // 处理认证错误
    $logger->error('京东认证错误: ' . $e->getMessage());
} catch (JdlConfigException $e) {
    // 处理配置错误
    $logger->error('京东配置错误: ' . $e->getMessage());
}
```

### 日志记录和监控

Bundle 自动记录所有 API 交互的详细信息：

- 请求参数（敏感数据已脱敏）
- 响应代码和内容
- 执行时间
- 错误详情

## 安全性

### API 凭证

**重要**：永远不要将 API 凭证提交到版本控制。使用环境变量或安全配置管理：

```yaml
# config/services.yaml
parameters:
    jd.app_key: '%env(JD_APP_KEY)%'
    jd.app_secret: '%env(JD_APP_SECRET)%'
    jd.customer_code: '%env(JD_CUSTOMER_CODE)%'
```

### 数据保护

- 日志中的所有敏感数据都会自动脱敏
- API 签名在调试输出中被隐藏
- 个人信息按照隐私标准处理

### 速率限制

了解京东 API 的速率限制：
- 实施适当的重试机制
- 使用适当的超时设置
- 监控 API 使用情况

## 异常

- `JdlApiException` - API 请求失败
- `JdlAuthException` - 认证错误
- `JdlConfigException` - 配置问题

## 贡献

我们欢迎贡献！请查看我们的贡献指南：

### 报告问题

- 使用 GitHub issue 跟踪器
- 提供清楚的重现步骤
- 包含环境详情（PHP/Symfony 版本）
- 附上相关日志或堆栈跟踪

### Pull Request

1. Fork 仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 按照我们的编码标准进行修改
4. 为新功能添加测试
5. 确保所有测试通过 (`composer test`)
6. 运行静态分析 (`composer phpstan`)
7. 提交 pull request

### 开发环境设置

```bash
# 克隆仓库
git clone https://github.com/tourze/jingdong-ldop-bundle.git
cd jingdong-ldop-bundle

# 安装依赖
composer install

# 运行测试
composer test

# 运行静态分析
composer phpstan
```

### 编码标准

- 遵循 PSR-12 编码标准
- 适当使用 PHP 8.1+ 特性
- 为新功能编写全面的测试
- 添加适当的类型提示和文档

## 更新日志

### 1.0.0 (当前版本)

**功能特性：**
- 初始发布
- 取件订单管理（创建/取消）
- 实时物流跟踪
- 全面的错误处理
- 所有 API 交互的审计日志
- Doctrine 实体管理

**依赖要求：**
- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+

**安全特性：**
- 日志中敏感数据自动脱敏
- 安全的 API 凭证处理
- 速率限制感知

## 许可证

此 Bundle 采用 MIT 许可证发布。详情请参阅 [LICENSE](LICENSE) 文件。
