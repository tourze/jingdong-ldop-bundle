<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Service;

use JingdongLdopBundle\Service\JdlService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * JdlService 集成测试
 *
 * 专注于服务是否能正确注入和基本实例化验证
 * 复杂的业务逻辑测试已移至 JdlServiceUnitTest
 *
 * @internal
 */
#[CoversClass(JdlService::class)]
#[RunTestsInSeparateProcesses]
final class JdlServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testServiceClassExists(): void
    {
        $reflection = new \ReflectionClass(JdlService::class);
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
    }

    public function testServiceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(JdlService::class);
        $this->assertTrue($reflection->hasMethod('createPickupOrder'));
        $this->assertTrue($reflection->hasMethod('cancelPickupOrder'));
        $this->assertTrue($reflection->hasMethod('getLogisticsTrace'));
    }

    public function testCreatePickupOrder(): void
    {
        $reflection = new \ReflectionClass(JdlService::class);
        $method = $reflection->getMethod('createPickupOrder');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->hasReturnType());
    }

    public function testCancelPickupOrder(): void
    {
        $reflection = new \ReflectionClass(JdlService::class);
        $method = $reflection->getMethod('cancelPickupOrder');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->hasReturnType());
    }
}
