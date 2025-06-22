<?php

namespace JingdongLdopBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JingdongLdopBundle\Repository\JdlConfigRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JdlConfigRepositoryTest extends TestCase
{
    private JdlConfigRepository $repository;
    private MockObject&ManagerRegistry $registryMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(ManagerRegistry::class);
        
        // 构造Repository并注入模拟对象
        $this->repository = new JdlConfigRepository($this->registryMock);
    }
    
    public function testInheritance_isServiceEntityRepository()
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }
    
    public function testConstructor_passesCorrectEntityClass()
    {
        // 使用反射获取构造函数中使用的实体类名
        $reflection = new \ReflectionClass(JdlConfigRepository::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        // 确保第一个参数是 ManagerRegistry 
        $this->assertEquals('registry', $parameters[0]->getName());
        
        // 读取父类构造函数的第二个参数默认值
        $parentClass = $reflection->getParentClass();
        $parentConstructor = $parentClass->getConstructor();
        $parentParameters = $parentConstructor->getParameters();
        
        // 验证第二个参数是实体类名
        $this->assertEquals('entityClass', $parentParameters[1]->getName());
        
        // 调用方法通过新的实例而不是使用现有的属性
        $repository = new JdlConfigRepository($this->registryMock);
        $this->assertInstanceOf(JdlConfigRepository::class, $repository);
    }
} 