<?php

declare(strict_types=1);

namespace JingdongLdopBundle\Tests\Service;

use JingdongLdopBundle\Service\JdlHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * JdlHttpClient 集成测试
 *
 * 专注于服务是否能正确注入和基本实例化验证
 * 网络请求相关的测试需要实际的 API 凭证，在此仅测试服务结构
 *
 * @internal
 */
#[CoversClass(JdlHttpClient::class)]
#[RunTestsInSeparateProcesses]
final class JdlHttpClientTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testServiceClassExists(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
    }

    public function testServiceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $this->assertTrue($reflection->hasMethod('getAuthCode'));
        $this->assertTrue($reflection->hasMethod('request'));
    }

    public function testGetAuthCodeMethod(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $method = $reflection->getMethod('getAuthCode');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->hasReturnType());
        $returnType = $method->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    public function testRequestMethod(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $method = $reflection->getMethod('request');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->hasReturnType());
        $returnType = $method->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function testGenerateSignMethodExists(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $this->assertTrue($reflection->hasMethod('generateSign'));
        $method = $reflection->getMethod('generateSign');
        $this->assertTrue($method->isPrivate());
    }

    public function testGetAccessTokenMethodExists(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $this->assertTrue($reflection->hasMethod('getAccessToken'));
        $method = $reflection->getMethod('getAccessToken');
        $this->assertTrue($method->isProtected());
    }

    public function testConstructorDependencies(): void
    {
        $reflection = new \ReflectionClass(JdlHttpClient::class);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $parameters = $constructor->getParameters();
        $this->assertCount(4, $parameters);

        $paramNames = array_map(fn ($p) => $p->getName(), $parameters);
        $this->assertContains('client', $paramNames);
        $this->assertContains('configRepository', $paramNames);
        $this->assertContains('logger', $paramNames);
        $this->assertContains('tokenRepository', $paramNames);
    }
}
