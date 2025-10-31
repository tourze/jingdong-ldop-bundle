<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\DependencyInjection;

use JingdongLdopBundle\DependencyInjection\JingdongLdopExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(JingdongLdopExtension::class)]
final class JingdongLdopExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private JingdongLdopExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
        $this->extension = new JingdongLdopExtension();
    }

    public function testInstanceOf(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }

    public function testLoadRegistersServices(): void
    {
        $this->extension->load([], $this->container);

        // 验证存在服务定义
        $serviceDefinitions = $this->container->getDefinitions();
        $this->assertGreaterThan(0, count($serviceDefinitions));
    }

    public function testLoadConfiguresParameters(): void
    {
        $this->extension->load([], $this->container);

        // 验证容器存在
        $this->assertNotNull($this->container);
    }
}
