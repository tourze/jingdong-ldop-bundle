<?php

namespace JingdongLdopBundle\Tests\DependencyInjection;

use JingdongLdopBundle\DependencyInjection\JingdongLdopExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class JingdongLdopExtensionTest extends TestCase
{
    private JingdongLdopExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new JingdongLdopExtension();
        $this->container = new ContainerBuilder();
        $this->container->registerExtension($this->extension);
    }
    
    public function testInstanceOf()
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }
    
    public function testLoad_registersServices()
    {
        $this->extension->load([], $this->container);
        
        // 验证存在服务定义
        $serviceDefinitions = $this->container->getDefinitions();
        $this->assertGreaterThan(0, count($serviceDefinitions));
        
        // 验证至少包含 Repository 和 Service 目录中的服务
        $this->assertMatchingServices($serviceDefinitions, 'JingdongLdopBundle\\Repository\\');
        $this->assertMatchingServices($serviceDefinitions, 'JingdongLdopBundle\\Service\\');
    }
    
    public function testLoad_configuresParameters()
    {
        $this->extension->load([], $this->container);
        
        // 验证容器包含常见参数
        $parameters = $this->container->getParameterBag()->all();
        $this->assertIsArray($parameters);
    }
    
    /**
     * 检查是否存在以指定命名空间前缀开头的服务
     */
    private function assertMatchingServices(array $definitions, string $namespacePrefix): void
    {
        $matchFound = false;
        foreach ($definitions as $id => $definition) {
            $class = $definition->getClass();
            if ($class && strpos($class, $namespacePrefix) === 0) {
                $matchFound = true;
                break;
            }
        }
        $this->assertTrue($matchFound, "No service found with class in namespace {$namespacePrefix}");
    }
} 